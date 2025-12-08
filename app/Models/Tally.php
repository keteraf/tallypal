<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TallyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $tally_id
 * @property string $name
 * @property string $description
 * @property int $owner_id
 */
class Tally extends Model
{
    /** @use HasFactory<TallyFactory> */
    use HasFactory;

    public const string TABLE_NAME = 'tallies';

    public const string KEYS_PREFIX = 'tally';

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, self::KEYS_PREFIX . '_' . User::KEYS_PREFIX, self::PRIMARY_KEY, 'user_id')
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
