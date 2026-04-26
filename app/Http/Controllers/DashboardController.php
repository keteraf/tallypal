<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return redirect()->route('home.dashboard');
        }

        $since = now()->subDays(29)->startOfDay();
        $dates = $this->buildDates();

        return Inertia::render('Dashboard', [
            'stats' => $this->getStats($since),
            'usersOverTime' => $this->buildTimeSeries($dates, $this->getCountsPerDay(User::query(), $since)),
            'talliesOverTime' => $this->buildTimeSeries($dates, $this->getCountsPerDay(Tally::query(), $since)),
            'transactionsOverTime' => $this->buildTimeSeries($dates, $this->getCountsPerDay(Transaction::query(), $since)),
        ]);
    }

    /** @return Collection<int, string> */
    private function buildDates(): Collection
    {
        /** @var Collection<int, string> */
        return collect(range(0, 29))
            ->map(fn (int $offset): string => now()->subDays(29 - $offset)->format('Y-m-d'));
    }

    /** @return array<string, int> */
    private function getStats(CarbonInterface $since): array
    {
        return [
            'total_users' => User::count(),
            'new_users_30d' => User::where('created_at', '>=', $since)->count(),
            'total_tallies' => Tally::count(),
            'shared_tallies' => Tally::has('users')->count(),
            'total_transactions' => Transaction::count(),
            'pending_invitations' => TallyInvitation::pending()->count(),
        ];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Collection<string, int>
     */
    private function getCountsPerDay(Builder $query, CarbonInterface $since): Collection
    {
        /** @var Collection<string, int> */
        return $query
            ->select(DB::raw('date(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->pluck('count', 'date');
    }

    /**
     * @param  Collection<int, string>  $dates
     * @param  Collection<string, int>  $rawData
     * @return array<int, array{date: string, count: int}>
     */
    private function buildTimeSeries(Collection $dates, Collection $rawData): array
    {
        return $dates->map(fn (string $date): array => [
            'date' => $date,
            'count' => (int) ($rawData[$date] ?? 0),
        ])->all();
    }
}
