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
}
