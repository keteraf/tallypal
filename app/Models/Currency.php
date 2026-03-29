<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read int $currency_id
 * @property string $uuid
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property int $decimals
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory, HasUuids;

    public const string TABLE_NAME = 'currencies';

    public const string KEYS_PREFIX = 'currency';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'decimals',
        'description',
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
