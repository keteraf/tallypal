<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * A migration is a frozen snapshot: it must keep producing the same schema when models, constants or
 * enums change later, so it names tables, columns and values literally and never references App\.
 *
 * @return array<int, string> One `file:line: code` entry per line of a migration that references App\.
 */
function migrationAppReferences(): array
{
    $offenders = [];

    foreach ((new Finder)->files()->in(dirname(__DIR__, 2) . '/database/migrations')->name('*.php')->sortByName() as $file) {
        foreach (preg_split('/\R/', $file->getContents()) ?: [] as $index => $line) {
            if (preg_match('/(?<![\w\\\\])\\\\?App\\\\/', $line) === 1) {
                $offenders[] = 'database/migrations/' . $file->getFilename() . ':' . ($index + 1) . ': ' . mb_trim($line);
            }
        }
    }

    return $offenders;
}

it('references no App\ class in any migration', function (): void {
    expect(migrationAppReferences())->toBe([]);
});
