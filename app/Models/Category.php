<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const string TABLE_NAME = 'categories';

    public const string KEYS_PREFIX = 'category';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $primaryKey = self::PRIMARY_KEY;
}
