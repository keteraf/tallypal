<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds two demo accounts for verifying the UI restyle against real data:
 *
 * - `demo@tallypal.test` — a populated account with a shared tally, a solo
 *   tally, a trip tally, months of transactions, and a pending invitation,
 *   so every list/chart/empty-state on every screen has something to show.
 *   It also owns "Fresh Start", a tally with no transactions, for checking the
 *   empty tally-detail state, and "City Break", which spends EUR and USD in the
 *   current month, for checking the per-currency figures (O-02, O-04).
 * - `demo-empty@tallypal.test` — a freshly registered account with no
 *   tallies, for checking empty states.
 *
 * Tallies carry fixed UUIDs, so reseeding never changes their URLs.
 *
 * Safe to re-run: it tears down any previously seeded demo data (by email)
 * before recreating it, so it does not require `migrate:fresh` first.
 */
class DemoSeeder extends Seeder
{
    public const string DEMO_PASSWORD = 'password';

    public const string DEMO_EMAIL = 'demo@tallypal.test';

    public const string DEMO_EMPTY_EMAIL = 'demo-empty@tallypal.test';

    public const string EMPTY_TALLY_NAME = 'Fresh Start';

    public const string MIXED_CURRENCY_TALLY_NAME = 'City Break';

    public const string THREE_MEMBER_TALLY_NAME = 'Ski Cabin';

    public const string HOUSEHOLD_UUID = '0d3e0000-0000-7000-8000-000000000001';

    public const string SOLO_UUID = '0d3e0000-0000-7000-8000-000000000002';

    public const string TRIP_UUID = '0d3e0000-0000-7000-8000-000000000003';

    public const string EMPTY_UUID = '0d3e0000-0000-7000-8000-000000000004';

    public const string MIXED_CURRENCY_UUID = '0d3e0000-0000-7000-8000-000000000005';

    public const string THREE_MEMBER_UUID = '0d3e0000-0000-7000-8000-000000000006';

    private const string PARTNER_EMAIL = 'priya.shah@tallypal.test';

    private const string TRIP_PARTNER_EMAIL = 'noah.whitfield@tallypal.test';

    public function run(): void
    {
        $this->call(ReferenceDataSeeder::class);
        $this->removeExistingDemoData();

        /** @var Collection<string, Currency> $currencies */
        $currencies = Currency::query()->get()->keyBy('code');
        /** @var Collection<string, Category> $categories */
        $categories = Category::query()->get()->keyBy('slug');

        $demo = User::query()->create([
            'name' => 'Sam Rivera',
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'email_verified_at' => now(),
            'default_currency_id' => $currencies->get('EUR')?->currency_id,
        ]);

        User::query()->create([
            'name' => 'Casey Doyle',
            'email' => self::DEMO_EMPTY_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'email_verified_at' => now(),
        ]);

        $priya = User::query()->create([
            'name' => 'Priya Shah',
            'email' => self::PARTNER_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'email_verified_at' => now(),
        ]);

        $noah = User::query()->create([
            'name' => 'Noah Whitfield',
            'email' => self::TRIP_PARTNER_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'email_verified_at' => now(),
        ]);

        $household = $this->makeTally($demo, self::HOUSEHOLD_UUID, 'Household', 'Rent, groceries, and everyday shared bills.', 'c1', '⌂');
        $this->addMember($household, $demo, 60.0, $priya, 40.0);

        $solo = $this->makeTally($demo, self::SOLO_UUID, 'Solo Savings', 'Personal spending kept separate from shared costs.', 'c4', '◐');

        $trip = $this->makeTally($demo, self::TRIP_UUID, 'Weekend Trip', 'A long weekend away, split evenly.', 'c3', '✈');
        $this->addMember($trip, $demo, 50.0, $noah, 50.0);
        $trip->update(['default_currency_id' => Currency::query()->where('code', 'USD')->value(Currency::PRIMARY_KEY)]);

        $this->makeTally($demo, self::EMPTY_UUID, self::EMPTY_TALLY_NAME, 'A brand-new tally with nothing logged yet.', 'c5', '☕');

        $cityBreak = $this->makeTally($demo, self::MIXED_CURRENCY_UUID, self::MIXED_CURRENCY_TALLY_NAME, 'A short city break paid partly in euros, partly in dollars.', 'c2', '◈');

        $skiCabin = $this->makeTally($demo, self::THREE_MEMBER_UUID, self::THREE_MEMBER_TALLY_NAME, 'A three-way EUR split: Noah is settled, Priya still owes you.', 'c6', '❄');
        $skiCabin->users()->attach($priya->id, ['default_share_percentage' => 30.0]);
        $skiCabin->users()->attach($noah->id, ['default_share_percentage' => 30.0]);
        $skiCabin->users()->updateExistingPivot($demo->id, ['default_share_percentage' => 40.0]);

        $this->seedHouseholdTransactions($household, $demo, $priya, $currencies, $categories);
        $this->seedSoloTransactions($solo, $demo, $currencies, $categories);
        $this->seedTripTransactions($trip, $demo, $noah, $currencies, $categories);
        $this->seedMixedCurrencyTransactions($cityBreak, $demo, $currencies, $categories);
        $this->seedThreeMemberTransactions($skiCabin, $demo, $priya, $noah, $currencies, $categories);
        $this->seedRecentHabits($household, $solo, $demo, $currencies, $categories);

        TallyInvitation::query()->create([
            'tally_id' => $household->tally_id,
            'invited_by_user_id' => $demo->id,
            'email' => 'morgan.ellis@example.com',
            'token' => Str::random(64),
            'status' => TallyInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
        ]);
    }

    private function removeExistingDemoData(): void
    {
        $userIds = User::query()
            ->whereIn('email', [self::DEMO_EMAIL, self::DEMO_EMPTY_EMAIL, self::PARTNER_EMAIL, self::TRIP_PARTNER_EMAIL])
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $tallyIds = Tally::query()->whereIn('owner_id', $userIds)->pluck(Tally::PRIMARY_KEY);

        TallyInvitation::query()->whereIn('tally_id', $tallyIds)->delete();
        Transaction::query()->whereIn('tally_id', $tallyIds)->delete();
        DB::table(TallyUser::TABLE_NAME)->whereIn(Tally::PRIMARY_KEY, $tallyIds)->delete();
        Tally::query()->whereIn(Tally::PRIMARY_KEY, $tallyIds)->delete();
        User::query()->whereIn('id', $userIds)->delete();
    }

    private function makeTally(User $owner, string $uuid, string $name, string $description, string $color, string $glyph): Tally
    {
        $tally = Tally::query()->create([
            'uuid' => $uuid,
            'owner_id' => $owner->id,
            'name' => $name,
            'description' => $description,
            'color' => $color,
            'glyph' => $glyph,
        ]);

        $tally->users()->attach($owner->id, ['default_share_percentage' => 100.0]);

        return $tally;
    }

    private function addMember(Tally $tally, User $owner, float $ownerShare, User $member, float $memberShare): void
    {
        $tally->users()->attach($member->id, ['default_share_percentage' => $memberShare]);
        $tally->users()->updateExistingPivot($owner->id, ['default_share_percentage' => $ownerShare]);
    }

    private function addTransaction(
        Tally $tally,
        User $party,
        Currency $currency,
        ?Category $category,
        TransactionType $type,
        string $title,
        float $amount,
        CarbonImmutable $date,
    ): Transaction {
        $transaction = Transaction::query()->create([
            'tally_id' => $tally->tally_id,
            'party_id' => $party->id,
            'currency_id' => $currency->currency_id,
            'category_id' => $category?->category_id,
            'type' => $type,
            'title' => $title,
            'amount' => $amount,
            'transaction_date' => $date->toDateString(),
        ]);

        $transaction->created_at = $date;
        $transaction->updated_at = $date;
        $transaction->save();

        return $transaction;
    }

    /**
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedHouseholdTransactions(Tally $tally, User $demo, User $priya, Collection $currencies, Collection $categories): void
    {
        $eur = $currencies->get('EUR');
        $usd = $currencies->get('USD');
        $now = CarbonImmutable::now();

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $monthAnchor = $now->subMonthsNoOverflow($monthsAgo);
            $lastDay = $monthsAgo === 0 ? $now->day : $monthAnchor->daysInMonth;

            $this->addTransaction($tally, $demo, $eur, $categories['rent'], TransactionType::EXPENSE, 'Rent', 950.00, $monthAnchor->day(1));

            if ($lastDay >= 5) {
                $this->addTransaction($tally, $demo, $eur, $categories['salary'], TransactionType::INCOME, 'Salary', 2650.00, $monthAnchor->day(5));
            }

            $entries = [
                ['day' => 7, 'party' => $priya, 'category' => 'groceries', 'title' => 'Weekly grocery run', 'amount' => 132.40, 'currency' => $eur],
                ['day' => 9, 'party' => $demo, 'category' => 'utilities', 'title' => 'Electric bill', 'amount' => 96.10, 'currency' => $eur],
                ['day' => 11, 'party' => $priya, 'category' => 'dining_out', 'title' => 'Dinner out', 'amount' => 54.20, 'currency' => $eur],
                ['day' => 14, 'party' => $demo, 'category' => 'groceries', 'title' => 'Grocery top-up', 'amount' => 88.75, 'currency' => $eur],
                ['day' => 17, 'party' => $priya, 'category' => 'entertainment', 'title' => 'Movie night', 'amount' => 28.00, 'currency' => $eur],
                ['day' => 19, 'party' => $demo, 'category' => 'household_supplies', 'title' => 'Cleaning supplies', 'amount' => 41.60, 'currency' => $eur],
                ['day' => 22, 'party' => $priya, 'category' => 'transportation', 'title' => 'Train pass', 'amount' => 64.00, 'currency' => $usd],
                ['day' => 24, 'party' => $demo, 'category' => 'dining_out', 'title' => 'Lunch with friends', 'amount' => 22.90, 'currency' => $eur],
                ['day' => 27, 'party' => $priya, 'category' => 'groceries', 'title' => 'Grocery run', 'amount' => 145.10, 'currency' => $eur],
            ];

            foreach ($entries as $entry) {
                if ($entry['day'] > $lastDay) {
                    continue;
                }

                $this->addTransaction(
                    $tally,
                    $entry['party'],
                    $entry['currency'],
                    $categories[$entry['category']],
                    TransactionType::EXPENSE,
                    $entry['title'],
                    $entry['amount'],
                    $monthAnchor->day($entry['day']),
                );
            }
        }

        // Guarantee coverage inside the current week for Home's weekly history and the Stats week range.
        $weekStart = $now->startOfWeek();
        $recentEntries = [
            ['offset' => 0, 'party' => $demo, 'category' => 'groceries', 'title' => 'Quick grocery stop', 'amount' => 34.20],
            ['offset' => 1, 'party' => $priya, 'category' => 'dining_out', 'title' => 'Coffee and pastry', 'amount' => 12.50],
            ['offset' => 3, 'party' => $demo, 'category' => 'transportation', 'title' => 'Bus tickets', 'amount' => 18.00],
        ];

        foreach ($recentEntries as $entry) {
            $date = $now->subDays($entry['offset']);

            if ($date->lessThan($weekStart)) {
                continue;
            }

            $this->addTransaction($tally, $entry['party'], $eur, $categories[$entry['category']], TransactionType::EXPENSE, $entry['title'], $entry['amount'], $date);
        }
    }

    /**
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedSoloTransactions(Tally $tally, User $demo, Collection $currencies, Collection $categories): void
    {
        $eur = $currencies->get('EUR');
        $now = CarbonImmutable::now();

        for ($monthsAgo = 3; $monthsAgo >= 0; $monthsAgo--) {
            $monthAnchor = $now->subMonthsNoOverflow($monthsAgo);
            $lastDay = $monthsAgo === 0 ? $now->day : $monthAnchor->daysInMonth;

            $entries = [
                ['day' => 4, 'category' => 'subscriptions', 'title' => 'Streaming service', 'amount' => 12.99],
                ['day' => 15, 'category' => 'personal_care', 'title' => 'Haircut', 'amount' => 35.00],
                ['day' => 21, 'category' => 'entertainment', 'title' => 'Concert ticket', 'amount' => 68.00],
            ];

            foreach ($entries as $entry) {
                if ($entry['day'] > $lastDay) {
                    continue;
                }

                $this->addTransaction($tally, $demo, $eur, $categories[$entry['category']], TransactionType::EXPENSE, $entry['title'], $entry['amount'], $monthAnchor->day($entry['day']));
            }
        }
    }

    /**
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedTripTransactions(Tally $tally, User $demo, User $noah, Collection $currencies, Collection $categories): void
    {
        $usd = $currencies->get('USD');
        $tripStart = CarbonImmutable::now()->subWeeks(3)->startOfWeek()->addDays(4);

        $entries = [
            ['dayOffset' => 0, 'party' => $demo, 'category' => 'travel', 'title' => 'Flight tickets', 'amount' => 420.00],
            ['dayOffset' => 0, 'party' => $noah, 'category' => 'travel', 'title' => 'Hotel booking', 'amount' => 260.00],
            ['dayOffset' => 1, 'party' => $demo, 'category' => 'dining_out', 'title' => 'Dinner out', 'amount' => 78.50],
            ['dayOffset' => 1, 'party' => $noah, 'category' => 'entertainment', 'title' => 'Museum tickets', 'amount' => 40.00],
            ['dayOffset' => 2, 'party' => $noah, 'category' => 'transportation', 'title' => 'Taxi fares', 'amount' => 32.00],
            ['dayOffset' => 2, 'party' => $demo, 'category' => 'dining_out', 'title' => 'Brunch', 'amount' => 45.20],
        ];

        foreach ($entries as $entry) {
            $this->addTransaction(
                $tally,
                $entry['party'],
                $usd,
                $categories[$entry['category']],
                TransactionType::EXPENSE,
                $entry['title'],
                $entry['amount'],
                $tripStart->addDays($entry['dayOffset']),
            );
        }
    }

    /**
     * EUR and USD expenses dated in the current month (the first and today, so they stay in the
     * month whatever day the seeder runs), so a tally card shows one line per currency.
     *
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedMixedCurrencyTransactions(Tally $tally, User $demo, Collection $currencies, Collection $categories): void
    {
        $eur = $currencies->get('EUR');
        $usd = $currencies->get('USD');
        $today = CarbonImmutable::now()->startOfDay();
        $monthStart = $today->startOfMonth();

        $this->addTransaction($tally, $demo, $eur, $categories['transportation'], TransactionType::EXPENSE, 'Train tickets', 86.40, $monthStart);
        $this->addTransaction($tally, $demo, $usd, $categories['travel'], TransactionType::EXPENSE, 'Hotel deposit', 210.00, $monthStart);
        $this->addTransaction($tally, $demo, $eur, $categories['entertainment'], TransactionType::EXPENSE, 'Museum pass', 32.00, $today);
        $this->addTransaction($tally, $demo, $usd, $categories['dining_out'], TransactionType::EXPENSE, 'Rooftop dinner', 64.50, $today);
    }

    /**
     * EUR 200 split 40/30/30: Noah paid exactly his share (settled), Priya paid
     * 20 of her 60 and so owes the owner 40.
     *
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedThreeMemberTransactions(Tally $tally, User $demo, User $priya, User $noah, Collection $currencies, Collection $categories): void
    {
        $eur = $currencies->get('EUR');
        $monthStart = CarbonImmutable::now()->startOfDay()->startOfMonth();

        $this->addTransaction($tally, $demo, $eur, $categories['travel'], TransactionType::EXPENSE, 'Cabin rental', 120.00, $monthStart);
        $this->addTransaction($tally, $noah, $eur, $categories['dining_out'], TransactionType::EXPENSE, 'Fondue night', 60.00, $monthStart);
        $this->addTransaction($tally, $priya, $eur, $categories['entertainment'], TransactionType::EXPENSE, 'Lift passes', 20.00, $monthStart);
    }

    /**
     * Dated relative to now, so the demo user's frequent categories (two or more uses in the last 90
     * days) stay populated whatever day the seeder runs: groceries, dining out and transportation lead
     * the expenses, Salary and Side work the incomes. Also the uncategorised rows: three expenses in
     * the current month (the ◌ row of the By category cards) and one income (the ↓ state).
     *
     * @param  Collection<string, Currency>  $currencies
     * @param  Collection<string, Category>  $categories
     */
    private function seedRecentHabits(Tally $household, Tally $solo, User $demo, Collection $currencies, Collection $categories): void
    {
        $eur = $currencies->get('EUR');
        $today = CarbonImmutable::now()->startOfDay();

        $expenses = [
            ['groceries', 'Market stall', 18.30, [1, 4, 6, 9, 13, 17, 21, 33, 47, 62]],
            ['dining_out', 'Takeaway', 15.80, [2, 8, 19, 29, 52, 70]],
            ['transportation', 'Taxi', 22.40, [3, 11, 26, 40, 66]],
        ];

        foreach ($expenses as [$slug, $title, $amount, $offsets]) {
            foreach ($offsets as $offset) {
                $this->addTransaction($household, $demo, $eur, $categories[$slug], TransactionType::EXPENSE, $title, $amount, $today->subDays($offset));
            }
        }

        $incomes = [
            [$solo, 'side_work', 'Freelance invoice', 320.00, [6, 23, 41, 70]],
            [$household, 'refunds_returns', 'Returned jacket', 45.90, [10]],
            [$solo, 'gifts_received', 'Birthday gift', 100.00, [35]],
            [$solo, 'investments', 'Dividend', 62.30, [28]],
            [$solo, 'sold_items', 'Sold bike lights', 85.00, [17]],
        ];

        foreach ($incomes as [$tally, $slug, $title, $amount, $offsets]) {
            foreach ($offsets as $offset) {
                $this->addTransaction($tally, $demo, $eur, $categories[$slug], TransactionType::INCOME, $title, $amount, $today->subDays($offset));
            }
        }

        $monthStart = $today->startOfMonth();

        $this->addTransaction($household, $demo, $eur, null, TransactionType::EXPENSE, 'Cash withdrawal', 160.00, $monthStart);
        $this->addTransaction($solo, $demo, $eur, null, TransactionType::EXPENSE, 'Misc. purchase', 18.50, $monthStart);
        $this->addTransaction($solo, $demo, $eur, null, TransactionType::EXPENSE, 'Parking', 6.00, $today);
        $this->addTransaction($solo, $demo, $eur, null, TransactionType::INCOME, 'Cash from a friend', 50.00, $today->subDays(15));
    }
}
