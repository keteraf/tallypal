<?php

declare(strict_types=1);

namespace App\Services;

/**
 * FP-04 / FP-09: the By category rows of Home and Stats. The top categories plus one "Other" row for
 * the rest, built on the server from the full breakdown so the Other amount and every percentage are
 * computed against the true total. Each bar is scaled to the largest named category so the top row is
 * full; "Other" is a remainder, not a category, so it never sets the scale and is capped at 100 when
 * it outweighs them all.
 */
final class CategoryBars
{
    /**
     * @param  array<int, array{name: string, slug: string|null, is_uncategorised: bool, amount: float, percentage: float}>  $categories  Every category, largest first.
     * @return array<int, array{name: string, slug: string|null, is_uncategorised: bool, amount: float, percentage: float, bar_fill: float, is_other: bool}>
     */
    public static function build(array $categories): array
    {
        $topCount = 5;
        $top = array_slice($categories, 0, $topCount);
        $restAmount = array_sum(array_column(array_slice($categories, $topCount), 'amount'));
        $total = array_sum(array_column($categories, 'amount'));
        $largestNamed = $top === [] ? 0.0 : max(array_column($top, 'amount'));
        $barFill = static fn (float $amount): float => $largestNamed > 0 ? min(100.0, $amount / $largestNamed * 100) : 0.0;

        $rows = array_map(
            static fn (array $category): array => [...$category, 'bar_fill' => $barFill($category['amount']), 'is_other' => false],
            $top,
        );

        if ($restAmount > 0) {
            $rows[] = [
                'name' => 'Other',
                'slug' => null,
                'is_uncategorised' => false,
                'amount' => $restAmount,
                'percentage' => round($restAmount / $total * 100, 1),
                'bar_fill' => $barFill($restAmount),
                'is_other' => true,
            ];
        }

        return $rows;
    }
}
