<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Which kind of transaction a category belongs to: a transaction's category must have its type.
 */
enum CategoryType: string
{
    case EXPENSE = 'expense';

    case INCOME = 'income';

    /**
     * The slugs the picker offers first until the user has used a category twice (design defaults).
     *
     * @return list<string>
     */
    public function defaultSlugs(): array
    {
        return match ($this) {
            self::EXPENSE => ['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions'],
            self::INCOME => ['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments'],
        };
    }
}
