<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatsRange;
use App\Models\Tally;
use App\Services\StatsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

use function abort_unless;

final class StatsController extends Controller
{
    public function __construct(private readonly StatsService $statsService) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless((bool) $user, 401);

        $request->validate([
            'range' => ['sometimes', new Enum(StatsRange::class)],
            'tally' => ['sometimes', 'uuid', Rule::exists(Tally::TABLE_NAME, 'uuid')],
        ]);

        $range = StatsRange::tryFrom((string) $request->query('range')) ?? StatsRange::MONTH;
        $tally = $request->filled('tally')
            ? Tally::query()->where('uuid', $request->string('tally')->toString())->firstOrFail()
            : null;

        if ($tally !== null) {
            $this->authorize('view', $tally);
        }

        return Inertia::render('Stats', $this->statsService->buildStatsPayload($user, $range, CarbonImmutable::now(), $tally));
    }
}
