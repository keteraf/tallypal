<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public const string TABLE_NAME = 'currencies';

    public const string KEYS_PREFIX = 'currency';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $primaryKey = self::PRIMARY_KEY;
}
