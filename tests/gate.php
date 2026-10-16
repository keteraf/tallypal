<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Quality gate
|--------------------------------------------------------------------------
|
| `php tests/gate.php full` (composer check:full) runs everything: the front-end build, vue-tsc, all of
| Vitest, lint, Rector, PHPStan, the PHP and browser tests on PostgreSQL with coverage, and mutation
| testing at 100% on SQLite. CI runs it on every pull request, never with test impact analysis.
|
| `php tests/gate.php fast` (composer check) runs only what the change affects: the PHP and browser
| tests through Pest TIA, every browser test when resources/ changed, `vitest related` on the changed
| front-end files, the build and vue-tsc only when their inputs changed, lint and static analysis only
| when PHP changed, and mutation only on the changed app/ classes that covers() / mutates() name.
| Changes are measured against master, except the files that force the full PHP suite, which are
| measured against the last green fast run.
|
| Neither mode starts the PostgreSQL container: both stop with a message when it is down.
|
*/

const GATE_ROOT = __DIR__ . '/..';
const GATE_STATE = GATE_ROOT . '/storage/framework/testing/gate.json';
const GATE_BASE_BRANCH = 'master';

/**
 * Paths whose change forces the full PHP suite (a fresh TIA baseline) on the next fast run.
 *
 * @var array<int, string>
 */
const GATE_FULL_SUITE_PATHS = [
    'config',
    'composer.json',
    'composer.lock',
    'phpunit.xml',
    'tests/Pest.php',
    'database/migrations',
    '.github/workflows',
];

/** Same values as phpunit.xml: the tests' PostgreSQL. */
const GATE_PGSQL = ['host' => '127.0.0.1', 'port' => 54329, 'database' => 'tallypal_test', 'username' => 'postgres', 'password' => 'postgres'];

/** Mutation testing runs on SQLite in memory: real environment variables win over phpunit.xml. */
const GATE_SQLITE_ENV = ['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'];

/**
 * Runs a command with the terminal attached and stops the gate with its exit code when it fails.
 *
 * @param  array<int, string>  $command
 * @param  array<string, string>  $env
 */
function gateRun(array $command, array $env = []): void
{
    fwrite(STDOUT, PHP_EOL . "\033[1m> " . implode(' ', array_map(escapeshellarg(...), $command)) . "\033[0m" . PHP_EOL);

    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes, GATE_ROOT, [...getenv(), ...$env]);
    $exitCode = is_resource($process) ? proc_close($process) : 1;

    if ($exitCode !== 0) {
        fwrite(STDERR, PHP_EOL . 'gate: failed (exit ' . $exitCode . ') at: ' . implode(' ', $command) . PHP_EOL);
        exit($exitCode);
    }
}

/**
 * @return array<int, string> Output lines of a git command, empty when it fails.
 */
function gateGit(string $arguments): array
{
    exec('git -C ' . escapeshellarg(GATE_ROOT) . ' ' . $arguments . ' 2>/dev/null', $lines, $exitCode);

    return $exitCode === 0 ? array_values(array_filter($lines, fn (string $line): bool => $line !== '')) : [];
}

/**
 * @return array<int, string> Project-relative files that differ from the merge base with master: committed,
 *                            staged, unstaged and untracked. Deleted files are included.
 */
function gateChangedFiles(): array
{
    $base = gateGit('merge-base ' . GATE_BASE_BRANCH . ' HEAD')[0] ?? null;

    if ($base === null) {
        fwrite(STDERR, 'gate: no merge base with ' . GATE_BASE_BRANCH . '.' . PHP_EOL);
        exit(1);
    }

    $files = [
        ...gateGit('diff --name-only ' . escapeshellarg($base)),
        ...gateGit('ls-files --others --exclude-standard'),
    ];
    sort($files);

    return array_values(array_unique($files));
}

/**
 * @param  array<int, string>  $files
 * @param  array<int, string>  $paths  Directories (matched with everything below them) or exact files.
 * @return array<int, string>
 */
function gateUnder(array $files, array $paths): array
{
    return array_values(array_filter($files, function (string $file) use ($paths): bool {
        foreach ($paths as $path) {
            if ($file === $path || str_starts_with($file, mb_rtrim($path, '/') . '/')) {
                return true;
            }
        }

        return false;
    }));
}

/**
 * A content hash of every file that forces the full PHP suite.
 */
function gateFullSuiteHash(): string
{
    $hashes = [];

    foreach (gateGit('ls-files --cached --others --exclude-standard -- ' . implode(' ', array_map(escapeshellarg(...), GATE_FULL_SUITE_PATHS))) as $file) {
        if (is_file(GATE_ROOT . '/' . $file)) {
            $hashes[$file] = (string) hash_file('xxh128', GATE_ROOT . '/' . $file);
        }
    }

    ksort($hashes);

    return hash('xxh128', (string) json_encode($hashes));
}

function gateStoredFullSuiteHash(): ?string
{
    $state = is_file(GATE_STATE) ? json_decode((string) file_get_contents(GATE_STATE), true) : null;

    return is_array($state) && is_string($state['full_suite_hash'] ?? null) ? $state['full_suite_hash'] : null;
}

function gateStoreFullSuiteHash(string $hash): void
{
    file_put_contents(GATE_STATE, (string) json_encode(['full_suite_hash' => $hash], JSON_PRETTY_PRINT) . PHP_EOL);
}

/**
 * Stops the gate when the tests' PostgreSQL is not reachable. The gate never starts it.
 */
function gateRequirePostgres(): void
{
    try {
        new PDO(
            sprintf('pgsql:host=%s;port=%d;dbname=%s;connect_timeout=3', GATE_PGSQL['host'], GATE_PGSQL['port'], GATE_PGSQL['database']),
            GATE_PGSQL['username'],
            GATE_PGSQL['password'],
        );
    } catch (PDOException $exception) {
        fwrite(STDERR, sprintf(
            'gate: PostgreSQL is not reachable on %s:%d (%s). Run `composer db:pgsql:up` first.' . PHP_EOL,
            GATE_PGSQL['host'],
            GATE_PGSQL['port'],
            mb_trim($exception->getMessage()),
        ));
        exit(1);
    }
}

/**
 * @return array<int, string> Fully qualified names of the classes some covers() / mutates() call names:
 *                            the mutation scope of the full gate.
 */
function gateMutationScope(): array
{
    $named = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if (! $file instanceof SplFileInfo || ! str_ends_with($file->getFilename(), 'Test.php')) {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        preg_match_all('/^use\s+([^;\s]+?)(?:\s+as\s+(\w+))?;/m', $source, $uses, PREG_SET_ORDER);
        $imports = [];
        foreach ($uses as $use) {
            $imports[$use[2] ?? basename(str_replace('\\', '/', $use[1]))] = $use[1];
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
 * @param  array<int, string>  $files  Changed project-relative paths.
 * @return array<int, string> Class names of the changed, still existing PHP files under app/.
 */
function gateChangedAppClasses(array $files): array
{
    $classes = [];

    foreach ($files as $file) {
        if (str_starts_with($file, 'app/') && str_ends_with($file, '.php') && is_file(GATE_ROOT . '/' . $file)) {
            $classes[] = 'App\\' . str_replace('/', '\\', mb_substr($file, 4, -4));
        }
    }

    return $classes;
}

/**
 * @param  array<int, string>  $ran
 * @param  array<int, string>  $skipped
 */
function gateSummary(string $mode, array $ran, array $skipped): string
{
    return sprintf(
        'gate %s: ran %s; skipped %s.',
        $mode,
        $ran === [] ? 'nothing' : implode(', ', $ran),
        $skipped === [] ? 'nothing' : implode(', ', $skipped),
    );
}

/** Pest refuses to start while a stale `->only()` lock from an interrupted run is around. */
function gateClearPestOnlyLock(): void
{
    @unlink(GATE_ROOT . '/vendor/pestphp/pest/.temp/only.lock');
}

function gateFull(): never
{
    gateRequirePostgres();
    $summary = gateSummary('full', ['build', 'vue-tsc', 'all Vitest', 'lint', 'rector', 'phpstan', 'PHP + browser tests on PostgreSQL (coverage)', 'mutation on SQLite at 100%'], []);
    fwrite(STDOUT, $summary . PHP_EOL);

    gateRun(['npm', 'run', 'build']);
    gateRun(['npx', 'vue-tsc', '--noEmit']);
    gateRun(['npm', 'run', 'test']);
    gateRun(['php', 'artisan', 'config:clear', '--ansi']);
    gateRun(['composer', 'test:lint']);
    gateRun(['composer', 'test:refactor']);
    gateRun(['composer', 'test:types']);
    gateClearPestOnlyLock();
    gateRun(['vendor/bin/pest', '--no-tia', '--colors=always', '--parallel', '--coverage', '--min=90'], ['XDEBUG_MODE' => 'coverage']);
    gateClearPestOnlyLock();
    gateRun(['vendor/bin/pest', '--no-tia', '--colors=always', '--parallel', '--mutate', '--min=100'], [...GATE_SQLITE_ENV, 'XDEBUG_MODE' => 'coverage']);

    fwrite(STDOUT, PHP_EOL . $summary . PHP_EOL);
    exit(0);
}

function gateFast(): never
{
    gateRequirePostgres();

    $changed = gateChangedFiles();
    $resources = gateUnder($changed, ['resources']);
    $buildInputs = [
        ...$resources,
        ...array_values(array_filter($changed, fn (string $file): bool => (bool) preg_match('#^(vite\.config\.[^/]+|tsconfig[^/]*\.json|package(-lock)?\.json)$#', $file))),
    ];
    $frontEndFiles = array_values(array_filter(
        $resources,
        fn (string $file): bool => (bool) preg_match('/\.(ts|js|mts|vue)$/', $file) && is_file(GATE_ROOT . '/' . $file),
    ));
    $phpChanged = array_values(array_filter(
        $changed,
        fn (string $file): bool => str_ends_with($file, '.php') || in_array($file, ['composer.lock', 'pint.json', 'rector.php', 'phpstan.neon'], true),
    ));
    $changedClasses = gateChangedAppClasses($changed);
    $scope = gateMutationScope();
    $mutationClasses = array_values(array_intersect($changedClasses, $scope));
    $outOfScope = array_values(array_diff($changedClasses, $scope));
    $fullSuiteHash = gateFullSuiteHash();
    $forceFullSuite = gateStoredFullSuiteHash() !== $fullSuiteHash;

    $ran = [];
    $skipped = [];

    if ($buildInputs !== [] || ! is_file(GATE_ROOT . '/public/build/manifest.json')) {
        $ran[] = 'build + vue-tsc (' . ($buildInputs === [] ? 'no build in public/' : count($buildInputs) . ' front-end/build file(s) changed') . ')';
    } else {
        $skipped[] = 'build + vue-tsc (nothing in resources/, vite config, tsconfig or package.json changed)';
    }

    if ($frontEndFiles !== []) {
        $ran[] = 'vitest related (' . count($frontEndFiles) . ' changed front-end file(s))';
    } else {
        $skipped[] = 'vitest (no changed .ts/.js/.vue file)';
    }

    if ($phpChanged !== []) {
        $ran[] = 'lint + rector + phpstan (PHP changed)';
    } else {
        $skipped[] = 'lint + rector + phpstan (no PHP change)';
    }

    $ran[] = $forceFullSuite
        ? 'full PHP + browser suite with a fresh TIA baseline (no baseline yet, or config/, composer.*, phpunit.xml, tests/Pest.php, migrations or CI workflow changed)'
        : 'PHP + browser tests via TIA (only the affected ones)';

    if ($resources !== []) {
        $ran[] = 'every browser test (resources/ changed; TIA follows PHP files only)';
    } else {
        $skipped[] = 'full browser suite (resources/ unchanged)';
    }

    if ($mutationClasses !== []) {
        $ran[] = 'mutation at 100% on ' . count($mutationClasses) . ' changed class(es)';
    } else {
        $skipped[] = $changedClasses === [] ? 'mutation (no app/ PHP file changed)' : 'mutation (no changed app/ class is named by covers()/mutates())';
    }

    if ($outOfScope !== [] && $mutationClasses !== []) {
        $skipped[] = 'mutation of ' . implode(', ', $outOfScope) . ' (not named by covers()/mutates())';
    }

    $summary = gateSummary('fast', $ran, $skipped);
    fwrite(STDOUT, $summary . PHP_EOL);

    if ($buildInputs !== [] || ! is_file(GATE_ROOT . '/public/build/manifest.json')) {
        gateRun(['npm', 'run', 'build']);
        gateRun(['npx', 'vue-tsc', '--noEmit']);
    }

    if ($frontEndFiles !== []) {
        gateRun(['npx', 'vitest', 'related', '--run', '--passWithNoTests', ...$frontEndFiles]);
    }

    if ($phpChanged !== []) {
        gateRun(['php', 'artisan', 'config:clear', '--ansi']);
        gateRun(['composer', 'test:lint']);
        gateRun(['composer', 'test:refactor']);
        gateRun(['composer', 'test:types']);
    }

    gateClearPestOnlyLock();
    gateRun(
        ['vendor/bin/pest', '--tia', ...($forceFullSuite ? ['--fresh'] : []), '--colors=always', '--parallel'],
        ['XDEBUG_MODE' => 'coverage'],
    );
    gateStoreFullSuiteHash($fullSuiteHash);

    if ($resources !== []) {
        gateClearPestOnlyLock();
        gateRun(['vendor/bin/pest', '--no-tia', '--colors=always', '--parallel', 'tests/Browser']);
    }

    if ($mutationClasses !== []) {
        gateClearPestOnlyLock();
        gateRun(
            ['vendor/bin/pest', '--no-tia', '--colors=always', '--parallel', '--mutate', '--class=' . implode(',', $mutationClasses), '--min=100', '--ignore-min-score-on-zero-mutations'],
            [...GATE_SQLITE_ENV, 'XDEBUG_MODE' => 'coverage'],
        );
    }

    fwrite(STDOUT, PHP_EOL . $summary . PHP_EOL);
    exit(0);
}

match ($argv[1] ?? null) {
    'full' => gateFull(),
    'fast' => gateFast(),
    default => (function (): never {
        fwrite(STDERR, 'Usage: php tests/gate.php full|fast' . PHP_EOL);
        exit(2);
    })(),
};
