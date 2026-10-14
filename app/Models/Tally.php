<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\TallyFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property-read int $tally_id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property int $owner_id
 * @property string|null $color
 * @property string|null $glyph
 * @property int|null $default_currency_id
 * @property float|null $income_total
 * @property float|null $expense_total
 * @property-read int|null $users_count
 *
 * @method static Builder<static> scopeForUser(Builder<static> $query, int $userId)
 * @method static Builder<static> scopeWithIncomeAndExpenseTotals(Builder<static> $query)
 * @method static Builder<static> scopeWithMembersCount(Builder<static> $query)
 */
class Tally extends Model
{
    /** @use HasFactory<TallyFactory> */
    use HasFactory, HasUuids;

    public const string TABLE_NAME = 'tallies';

    public const string KEYS_PREFIX = 'tally';

    /**
     * The glyph catalogue offered by the tally picker (text presentation, not emoji).
     * Mirrors `TALLY_GLYPHS` in `resources/js/lib/category.ts`.
     *
     * @var list<string>
     */
    public const array GLYPHS = ['⌂', '✈', '◐', '◈', '♪', '☕']; // @pest-mutate-ignore

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    /** @pest-mutate-ignore */
    protected $fillable = [
        'owner_id', // @pest-mutate-ignore
        'name', // @pest-mutate-ignore
        'description', // @pest-mutate-ignore
        'color', // @pest-mutate-ignore
        'glyph', // @pest-mutate-ignore
        'default_currency_id', // @pest-mutate-ignore
    ];

    /** @pest-mutate-ignore */
    protected $guarded = [
        'income_total', // @pest-mutate-ignore
        'expense_total', // @pest-mutate-ignore
    ];

    /** @pest-mutate-ignore */
    protected $casts = [
        'income_total' => 'float', // @pest-mutate-ignore
        'expense_total' => 'float', // @pest-mutate-ignore
    ];

    /**
     * @param  Builder<Tally>  $query
     */
    #[Scope]
    public function forUser(Builder $query, int $userId): void
    {
        $query->where(function (Builder $q) use ($userId): void {
            $q->where('owner_id', $userId)
                ->orWhereHas('users', fn (Builder $q) => $q->where('user_id', $userId));
        });
    }

    /**
     * @param  Builder<Tally>  $query
     */
    #[Scope]
    public function withIncomeAndExpenseTotals(Builder $query): void
    {
        $query->addSelect([
            'income_total' => DB::table(Transaction::TABLE_NAME)
                ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                ->selectRaw('COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0)')
                ->whereColumn('transactions.tally_id', 'tallies.tally_id')
                ->where('transactions.type', TransactionType::INCOME->value),
            'expense_total' => DB::table(Transaction::TABLE_NAME)
                ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                ->selectRaw('COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0)')
                ->whereColumn('transactions.tally_id', 'tallies.tally_id')
                ->where('transactions.type', TransactionType::EXPENSE->value),
        ]);
    }

    /**
     * The single definition of a tally's members: its distinct users, the owner counted once
     * whether or not they also have a `tally_user` row. Selected as `users_count`.
     *
     * @param  Builder<Tally>  $query
     */
    #[Scope]
    public function withMembersCount(Builder $query): void
    {
        $query->addSelect([
            'users_count' => DB::table(TallyUser::TABLE_NAME)
                ->selectRaw('1 + COUNT(DISTINCT tally_user.user_id)')
                ->whereColumn('tally_user.tally_id', 'tallies.tally_id')
                ->whereColumn('tally_user.user_id', '!=', 'tallies.owner_id'),
        ]);
    }

    public function membersCount(): int
    {
        $count = self::query()->withMembersCount()->whereKey($this->getKey())->value('users_count');

        return is_numeric($count) ? (int) $count : 1;
    }

    /**
     * The members as models, in the `withMembersCount()` definition: the owner first, then the other
     * members by id, each counted once. Uses the `owner` and `users` relations (loaded when missing).
     *
     * @return Collection<int, User>
     */
    public function membersInOrder(): Collection
    {
        /** @var Collection<int, User> $others */
        $others = $this->users->where('id', '!=', $this->owner_id)->sortBy('id');

        return collect([$this->owner])->filter()->merge($others)->values();
    }

    /**
     * Each member's default share percentage, keyed by user id in `membersInOrder()` order. An owner
     * without a `tally_user` row takes the remainder of the others' shares (never below 0).
     *
     * @return array<int, float>
     */
    public function memberShares(): array
    {
        $pivoted = [];

        foreach ($this->users as $user) {
            /** @var TallyUser $pivot */
            $pivot = $user->pivot;
            $pivoted[$user->id] = (float) $pivot->default_share_percentage;
        }

        $others = array_sum(array_filter($pivoted, fn (int $userId): bool => $userId !== $this->owner_id, ARRAY_FILTER_USE_KEY));

        return $this->membersInOrder()
            ->mapWithKeys(fn (User $member): array => [$member->id => $pivoted[$member->id] ?? max(0.0, 100.0 - $others)])
            ->all();
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
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The currency Add transaction prefills for this tally; a default only, never a restriction.
     *
     * @return BelongsTo<Currency, $this>
     */
    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', Currency::PRIMARY_KEY);
    }

    /**
     * The currency Add transaction prefills, in order: this tally's default, this tally's most-used
     * currency (ties go to the lowest id), the user's default, then the app default (`app.default_currency`). It is only a prefill.
     */
    public function prefillCurrency(?User $user): ?Currency
    {
        if ($this->default_currency_id !== null) {
            return $this->defaultCurrency;
        }

        $mostUsedId = $this->transactions()
            ->selectRaw('currency_id, COUNT(*) as uses')
            ->groupBy('currency_id')
            ->orderByDesc('uses')
            ->orderBy('currency_id')
            ->value('currency_id');

        if ($mostUsedId !== null) {
            return Currency::query()->whereKey($mostUsedId)->first();
        }

        return $user->defaultCurrency ?? Currency::appDefault();
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

    /**
     * @return HasMany<TallyInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TallyInvitation::class, self::PRIMARY_KEY, self::PRIMARY_KEY);
    }
}
