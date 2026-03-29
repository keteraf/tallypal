<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TallyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $tally_id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property int $owner_id
 * @property mixed $income_total
 * @property mixed $expense_total
 */
class Tally extends Model
{
    /** @use HasFactory<TallyFactory> */
    use HasFactory, HasUuids;

    public const string TABLE_NAME = 'tallies';

    public const string KEYS_PREFIX = 'tally';

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'owner_id', // @pest-mutate-ignore
        'name', // @pest-mutate-ignore
        'description', // @pest-mutate-ignore
    ];

    /** @pest-mutate-ignore */
    protected $guarded = [
        'income_total', // @pest-mutate-ignore
        'expense_total', // @pest-mutate-ignore
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this, TallyUser, 'pivot'>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, self::KEYS_PREFIX . '_' . User::KEYS_PREFIX, self::PRIMARY_KEY, 'user_id')
            ->using(TallyUser::class)
            ->withPivot('default_share_percentage')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, self::PRIMARY_KEY, self::PRIMARY_KEY);
    }
}
