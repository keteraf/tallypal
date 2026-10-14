<?php

declare(strict_types=1);

use App\Services\CategoryBars;

covers(CategoryBars::class);

/**
 * @param  array<int, float>  $amounts
 * @return array<int, array{name: string, amount: float, percentage: float}>
 */
function categoriesOf(array $amounts): array
{
    return array_map(
        static fn (int $index, float $amount): array => ['name' => 'Cat' . ($index + 1), 'amount' => $amount, 'percentage' => 0.0],
        array_keys($amounts),
        $amounts,
    );
}

test('no categories give no rows', function (): void {
    expect(CategoryBars::build([]))->toBe([]);
});

test('five or fewer categories get no Other row and bars relative to the largest', function (): void {
    $rows = CategoryBars::build(categoriesOf([100.0, 50.0, 25.0]));

    expect($rows)->toHaveCount(3)
        ->and(array_column($rows, 'bar_fill'))->toBe([100.0, 50.0, 25.0])
        ->and(array_column($rows, 'is_other'))->toBe([false, false, false]);
});

test('the tail beyond the top five becomes one Other row with the summed amount and its share of the true total', function (): void {
    $rows = CategoryBars::build(categoriesOf([40.0, 30.0, 20.0, 10.0, 5.0, 3.0, 2.0, 1.0]));

    expect($rows)->toHaveCount(6)
        ->and($rows[5])->toBe(['name' => 'Other', 'amount' => 6.0, 'percentage' => 5.4, 'bar_fill' => 15.0, 'is_other' => true])
        ->and(array_sum(array_column($rows, 'amount')))->toBe(111.0);
});

test('Other never sets the scale and is capped at 100 when it outweighs the largest category', function (): void {
    $rows = CategoryBars::build(categoriesOf([100.0, 90.0, 80.0, 70.0, 60.0, 30.0, 30.0, 30.0, 30.0, 30.0, 30.0]));

    expect($rows[0]['bar_fill'])->toBe(100.0)
        ->and($rows[4]['bar_fill'])->toBe(60.0)
        ->and($rows[5])->toMatchArray(['name' => 'Other', 'amount' => 180.0, 'bar_fill' => 100.0, 'is_other' => true]);
});

test('a zero rest adds no Other row', function (): void {
    $rows = CategoryBars::build(categoriesOf([10.0, 9.0, 8.0, 7.0, 6.0, 0.0]));

    expect($rows)->toHaveCount(5);
});

test('exactly five categories fill the named rows and a sixth is the first to fall into Other', function (): void {
    $fiveRows = CategoryBars::build(categoriesOf([50.0, 40.0, 30.0, 20.0, 10.0]));
    $sixRows = CategoryBars::build(categoriesOf([50.0, 40.0, 30.0, 20.0, 10.0, 5.0]));

    expect($fiveRows)->toHaveCount(5)
        ->and(array_column($fiveRows, 'is_other'))->each->toBeFalse()
        ->and($sixRows)->toHaveCount(6)
        ->and(array_column($sixRows, 'name'))->toBe(['Cat1', 'Cat2', 'Cat3', 'Cat4', 'Cat5', 'Other'])
        ->and($sixRows[5])->toMatchArray(['amount' => 5.0, 'is_other' => true]);
});
