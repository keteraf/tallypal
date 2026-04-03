<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $tally_invitation_id
 * @property string $uuid
 * @property int $tally_id
 * @property int $invited_by_user_id
 * @property string $email
 * @property string $token
 * @property string $status
 * @property Carbon|null $expires_at
 */
class TallyInvitation extends Model
{
    use HasUuids;

    public const string TABLE_NAME = 'tally_invitations';

    /** @pest-mutate-ignore */
    public const string KEYS_PREFIX = 'tally_invitation'; // @pest-mutate-ignore

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_ACCEPTED = 'accepted';

    public const string STATUS_DECLINED = 'declined';

    /** @var array<int, string> */
    public const array STATUSES = [
        self::STATUS_PENDING, // @pest-mutate-ignore
        self::STATUS_ACCEPTED, // @pest-mutate-ignore
        self::STATUS_DECLINED, // @pest-mutate-ignore
    ];

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'tally_id', // @pest-mutate-ignore
        'invited_by_user_id', // @pest-mutate-ignore
        'email', // @pest-mutate-ignore
        'token', // @pest-mutate-ignore
        'status', // @pest-mutate-ignore
        'expires_at', // @pest-mutate-ignore
    ];

    /** @return array<int, string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @param  Builder<TallyInvitation>  $query
     * @return Builder<TallyInvitation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param  Builder<TallyInvitation>  $query
     * @return Builder<TallyInvitation>
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
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
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
