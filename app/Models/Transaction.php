<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $transaction_id
 * @property int $tally_id
 * @property int $party_id
 * @property int $currency_id
 * @property int $category_id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property float $amount
 * @property string $transaction_date
 * @property string $created_at
 * @property string $updated_at
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const string TABLE_NAME = 'transactions';

    public const string KEYS_PREFIX = 'transaction';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'tally_id',
        'party_id',
        'currency_id',
        'category_id',
        'type',
        'title',
        'description',
        'amount',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'type' => TransactionType::class,
    ];

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
}
