<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $category_id
 * @property string $uuid
 * @property string $name
 */
class Category extends Model
{
    use HasUuids;

    public const string TABLE_NAME = 'categories';

    public const string KEYS_PREFIX = 'category';

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'name', // @pest-mutate-ignore
        'icon', // @pest-mutate-ignore
        'color', // @pest-mutate-ignore
    ];

    /** @return array<int, string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
