<?php

declare(strict_types=1);

// Workaround for pest-plugin-mutate timing issue: the covers() function resolves
// MutationTestRunner from the container, but the Mutate plugin boots after test files load.
// Register a disabled stub so covers() doesn't throw; the real runner overwrites it later.
Pest\Support\Container::getInstance()->add(
    Pest\Mutate\Contracts\MutationTestRunner::class,
    new class implements Pest\Mutate\Contracts\MutationTestRunner
    {
        public function enable(): void {}

        public function isEnabled(): bool
        {
            return false;
        }

        public function setOriginalArguments(array $arguments): void {}

        public function setStartTime(float $startTime): void {}

        public function isCodeCoverageRequested(): bool
        {
            return false;
        }

        public function run(): int
        {
            return 0;
        }
    }
);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->group('feature', 'unit')
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
