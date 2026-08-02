<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tally\DestroyTallyAction;
use App\Actions\Tally\StoreTallyAction;
use App\Actions\Tally\UpdateTallyAction;
use App\Contracts\Repositories\TallyRepositoryInterface;
use App\DTOs\TallyDTO;
use App\Http\Requests\Tally\DestroyTallyRequest;
use App\Http\Requests\Tally\StoreTallyRequest;
use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class TallyController extends Controller
{
    public function __construct(private readonly TallyRepositoryInterface $tallyRepository) {}

    /**
     * Display a listing of the tallies.
     */
    public function index(Request $request, HomeService $homeService): Response
    {
        /** @var User $user */
        $user = $request->user(); // @pest-mutate-ignore
        $myBalances = $homeService->myBalances($user);

        return Inertia::render('tallies/Index', [
            'tallies' => $this->tallyRepository->accessibleTalliesWithTotals($user->id)
                ->map(fn (array $tally): array => [...$tally, 'my_balance' => $myBalances[$tally['uuid']] ?? []]),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the form for creating a new tally.
     */
    public function create(): Response
    {
        return Inertia::render('tallies/Create', [
            'currencies' => Currency::all(['currency_id', 'uuid', 'name', 'code', 'symbol']),
            'default_currency_uuid' => auth()->user()?->defaultCurrency?->uuid,
        ]);
    }

    /**
     * Store a newly created tally in storage.
     *
     * @throws Throwable
     */
    public function store(StoreTallyRequest $request, StoreTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null, color?: string|null, glyph?: string|null, default_currency_id?: int|null} $data */
        $data = [
            'owner_id' => (int) $request->user()?->id, // @pest-mutate-ignore
            ...$request->validated(),
        ];

        $action->handle(TallyDTO::fromArray($data));

        return redirect()->route('tallies.index');
    }

    /**
     * Show the form for editing the specified tally.
     */
    public function edit(Request $request, Tally $tally): Response
    {
        $this->authorize('update', $tally);

        return Inertia::render('tallies/Edit', [
            'tally' => [
                ...$tally->only(['tally_id', 'uuid', 'name', 'description', 'owner_id', 'color', 'glyph']),
                'default_currency_uuid' => $tally->defaultCurrency?->uuid,
            ],
            'currencies' => Currency::all(['currency_id', 'uuid', 'name', 'code', 'symbol']),
            'other_members_count' => $tally->users()->whereKeyNot($request->user()?->id)->count(),
        ]);
    }

    /**
     * Return the current user's tallies as JSON for the tally picker.
     */
    public function picker(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user(); // @pest-mutate-ignore

        $tallies = Tally::query()
            ->forUser($user->id)
            ->get(['tally_id', 'uuid', 'name', 'color', 'glyph'])
            ->map(fn (Tally $tally): array => [
                'tally_id' => $tally->tally_id,
                'uuid' => $tally->uuid,
                'name' => $tally->name,
                'color' => $tally->color,
                'glyph' => $tally->glyph,
            ])
            ->values();

        return response()->json($tallies);
    }

    /**
     * Update the specified tally in storage.
     *
     * @throws Throwable
     */
    public function update(UpdateTallyRequest $request, Tally $tally, UpdateTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null, color?: string|null, glyph?: string|null, default_currency_id?: int|null} $data */
        $data = [
            'owner_id' => (int) $tally->owner_id, // @pest-mutate-ignore
            'default_currency_id' => $tally->default_currency_id,
            ...$request->validated(),
        ];

        $action->handle($tally, TallyDTO::fromArray($data));

        return redirect()->route('tallies.transactions.index', $tally);
    }

    /**
     * Remove the specified tally from storage.
     *
     * @throws Throwable
     */
    public function destroy(DestroyTallyRequest $request, Tally $tally, DestroyTallyAction $action): RedirectResponse
    {
        $action->handle($tally);

        return redirect()->route('tallies.index')->with('status', 'tally-deleted');
    }
}
