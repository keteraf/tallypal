<?php

declare(strict_types=1);

namespace App\Enums;

enum StatsRange: string
{
    case WEEK = 'week';

    case MONTH = 'month';

    case YEAR = 'year';
}
