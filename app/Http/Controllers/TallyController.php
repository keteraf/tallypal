<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tally\DestroyTallyAction;
use App\Actions\Tally\StoreTallyAction;
use App\Actions\Tally\UpdateTallyAction;
use App\DTOs\TallyDTO;
use App\Http\Requests\Tally\DestroyTallyRequest;
use App\Http\Requests\Tally\StoreTallyRequest;
use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Tally;
use App\Models\User;
use App\Repositories\Contracts\TallyRepositoryInterface;
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
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user(); // @pest-mutate-ignore

        return Inertia::render('tallies/Index', [
            'tallies' => $this->tallyRepository->accessibleTalliesWithTotals($user->id),
        ]);
    }

    /**
     * Show the form for creating a new tally.
     */
    public function create(): Response
    {
        return Inertia::render('tallies/Create');
    }

    /**
     * Store a newly created tally in storage.
     *
     * @throws Throwable
     */
    public function store(StoreTallyRequest $request, StoreTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null} $data */
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
    public function edit(Tally $tally): Response
    {
        $this->authorize('update', $tally);

        return Inertia::render('tallies/Edit', [
            'tally' => $tally,
        ]);
    }

    /**
     * Update the specified tally in storage.
     *
     * @throws Throwable
     */
    public function update(UpdateTallyRequest $request, Tally $tally, UpdateTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null} $data */
        $data = [
            'owner_id' => (int) $tally->owner_id, // @pest-mutate-ignore
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

        return redirect()->route('tallies.index');
    }
}
