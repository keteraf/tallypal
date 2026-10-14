<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $transaction_id
 * @property string $uuid
 * @property int $tally_id
 * @property int $party_id
 * @property int $currency_id
 * @property int|null $category_id
 * @property TransactionType $type
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $transaction_date
 * @property string $created_at
 * @property string $updated_at
 * @property-read Currency|null $currency
 * @property-read float $raw_total
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasUuids;

    public const string TABLE_NAME = 'transactions';

    public const string KEYS_PREFIX = 'transaction';

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    public const string NORMALIZED_AMOUNT_EXPRESSION = 'transactions.amount / POWER(10, currencies.decimals)';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'tally_id', // @pest-mutate-ignore
        'party_id', // @pest-mutate-ignore
        'currency_id', // @pest-mutate-ignore
        'category_id', // @pest-mutate-ignore
        'type', // @pest-mutate-ignore
        'title', // @pest-mutate-ignore
        'description', // @pest-mutate-ignore
        'amount', // @pest-mutate-ignore
        'transaction_date', // @pest-mutate-ignore
    ];

    /** @pest-mutate-ignore */
    protected $casts = [
        'amount' => 'float', // @pest-mutate-ignore
        'type' => TransactionType::class, // @pest-mutate-ignore
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
     * @return BelongsTo<Tally, $this>
     */
    public function tally(): BelongsTo
    {
        return $this->belongsTo(Tally::class, Tally::PRIMARY_KEY, Tally::PRIMARY_KEY);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(User::class, 'party_id', 'id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', Currency::PRIMARY_KEY);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', Category::PRIMARY_KEY);
    }

    /**
     * @return Attribute<float, float>
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): float {
                $decimals = $this->currency->decimals ?? 2;
                $factor = 10 ** $decimals;

                $numericValue = is_numeric($value) ? (float) $value : 0.0; // @pest-mutate-ignore

                return $numericValue / $factor;
            },
            set: function (mixed $value): float {
                $decimals = $this->currency->decimals ?? 2;
                $factor = 10 ** $decimals;

                $numericValue = is_numeric($value) ? (float) $value : 0.0; // @pest-mutate-ignore

                return $numericValue * $factor;
            }
        );
    }
}
