<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data, seeded by `ReferenceDataSeeder` and matched by `slug`. The UI translates the label
 * by slug (`categories.<slug>`); `name` is the English fallback.
 *
 * @property-read int $category_id
 * @property string $uuid
 * @property CategoryType $type
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasUuids;

    public const string TABLE_NAME = 'categories';

    public const string KEYS_PREFIX = 'category';

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'type', // @pest-mutate-ignore
        'slug', // @pest-mutate-ignore
        'name', // @pest-mutate-ignore
        'description', // @pest-mutate-ignore
        'icon', // @pest-mutate-ignore
        'color', // @pest-mutate-ignore
    ];

    /** @pest-mutate-ignore */
    protected $casts = [
        'type' => CategoryType::class, // @pest-mutate-ignore
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

    /**
     * Categories a transaction of the given type may use.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    public function ofType(Builder $query, CategoryType $type): void
    {
        $query->where('type', $type->value);
    }
}
