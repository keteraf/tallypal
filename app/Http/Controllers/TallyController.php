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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TallyController extends Controller
{
    /**
     * Display a listing of the tallies.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()?->id;

        $tallies = Tally::query()
            ->where(function ($query) use ($userId): void {
                $query->where('owner_id', $userId)
                    ->orWhereHas('users', fn ($query) => $query->where('user_id', $userId));
            })
            ->with(['owner'])
            ->withSum(['transactions as income_total' => fn (\Illuminate\Contracts\Database\Eloquent\Builder $query) => $query->where('type', 'income')], 'amount')
            ->withSum(['transactions as expense_total' => fn (\Illuminate\Contracts\Database\Eloquent\Builder $query) => $query->where('type', 'expense')], 'amount')
            ->get()
            ->map(function (Tally $tally): Tally {
                $incomeTotal = $tally->income_total;
                $tally->income_total = (is_numeric($incomeTotal) ? (float) $incomeTotal : 0.0) / 100;

                $expenseTotal = $tally->expense_total;
                $tally->expense_total = (is_numeric($expenseTotal) ? (float) $expenseTotal : 0.0) / 100;

                return $tally;
            });

        return Inertia::render('tallies/Index', [
            'tallies' => $tallies,
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
     */
    public function store(StoreTallyRequest $request, StoreTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null} $data */
        $data = [
            'owner_id' => (int) $request->user()?->id,
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
     */
    public function update(UpdateTallyRequest $request, Tally $tally, UpdateTallyAction $action): RedirectResponse
    {
        /** @var array{owner_id: int, name: string, description?: string|null} $data */
        $data = [
            'owner_id' => (int) $tally->owner_id,
            ...$request->validated(),
        ];

        $action->handle($tally, TallyDTO::fromArray($data));

        return redirect()->route('tallies.transactions.index', $tally);
    }

    /**
     * Remove the specified tally from storage.
     */
    public function destroy(DestroyTallyRequest $request, Tally $tally, DestroyTallyAction $action): RedirectResponse
    {
        $action->handle($tally);

        return redirect()->route('tallies.index');
    }
}
