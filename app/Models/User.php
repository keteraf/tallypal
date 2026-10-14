<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $email_verified_at
 * @property int|null $default_currency_id
 * @property string|null $remember_token
 * @property-read Collection<int, Tally> $ownedTallies
 * @property-read Collection<int, Tally> $tallies
 * @property TallyUser|null $pivot
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable;

    public const string TABLE_NAME = 'users';

    public const string KEYS_PREFIX = 'user';

    public const string PRIMARY_KEY = 'id';

    protected $primaryKey = self::PRIMARY_KEY;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'default_currency_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Up to two uppercase initials of the name, `··` when it is blank.
     */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', mb_strtoupper(mb_trim($this->name)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $initials = collect($parts)->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode('');

        return $initials === '' ? '··' : $initials;
    }

    /**
     * Stable avatar colour token (`c1`…`c6`) for this user.
     */
    public function avatarColor(): string
    {
        return 'c' . ($this->id % 6 + 1);
    }

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
     * @return BelongsTo<Currency, $this>
     */
    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', Currency::PRIMARY_KEY);
    }

    /**
     * @return HasMany<Tally, $this>
     */
    public function ownedTallies(): HasMany
    {
        return $this->hasMany(Tally::class, 'owner_id', 'id');
    }

    /**
     * @return BelongsToMany<Tally, $this>
     */
    public function tallies(): BelongsToMany
    {
        return $this->belongsToMany(Tally::class, Tally::KEYS_PREFIX . '_' . self::KEYS_PREFIX, 'user_id', Tally::PRIMARY_KEY);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
