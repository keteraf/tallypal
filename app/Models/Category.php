<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 */
class Category extends Model
{
    use HasUuids;

    public const string TABLE_NAME = 'categories';

    public const string KEYS_PREFIX = 'category';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'name',
        'icon',
        'color',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
