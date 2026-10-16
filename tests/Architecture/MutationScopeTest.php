<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * Pest only mutates classes named in covers() / mutates(), so a class named by no test gets no mutants.
 *
 * @return array<int, string> Fully qualified names of every class named by a covers() / mutates() call.
 */
function mutationScopeNamedClasses(): array
{
    $named = [];

    foreach ((new Finder)->files()->in(dirname(__DIR__))->name('*Test.php') as $file) {
        $source = $file->getContents();

        preg_match_all('/^use\s+([^;\s]+?)(?:\s+as\s+(\w+))?;/m', $source, $uses, PREG_SET_ORDER);
        $imports = [];
        foreach ($uses as $use) {
            $imports[$use[2] ?? class_basename($use[1])] = $use[1];
        }

        preg_match_all('/\b(?:covers|mutates)\(([^;]*?)\);/s', $source, $calls);
        foreach ($calls[1] as $arguments) {
            preg_match_all('/\\\\?([\w\\\\]+)::class/', $arguments, $classes);
            foreach ($classes[1] as $class) {
                $named[] = $imports[$class] ?? mb_ltrim($class, '\\');
            }
        }
    }

    return array_values(array_unique($named));
}

/**
 * @return array<int, string> Fully qualified names of the classes that must be named.
 */
function mutationScopeRequiredClasses(): array
{
    $required = [];

    foreach (['Actions', 'Rules', 'Services', 'Observers', 'Console'] as $directory) {
        foreach ((new Finder)->files()->in(dirname(__DIR__, 2) . '/app/' . $directory)->name('*.php') as $file) {
            $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $required[] = 'App\\' . $directory . '\\' . $relative;
        }
    }

    return $required;
}

it('names every class in the mutation scope directories in some covers() or mutates()', function (): void {
    $offenders = array_values(array_diff(mutationScopeRequiredClasses(), mutationScopeNamedClasses()));

    expect($offenders)->toBe([]);
});
