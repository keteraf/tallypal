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
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class TallyController extends Controller
{
    /**
     * Display a listing of the tallies.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()?->id; // @pest-mutate-ignore

        $tallies = Tally::query()
            ->where(function ($query) use ($userId): void {
                $query->where('owner_id', $userId)
                    ->orWhereHas('users', fn ($query) => $query->where('user_id', $userId));
            })
            ->with(['owner'])
            ->addSelect([
                'income_total' => DB::table('transactions')
                    ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                    ->selectRaw('COALESCE(SUM(transactions.amount / POWER(10, currencies.decimals)), 0)')
                    ->whereColumn('transactions.tally_id', 'tallies.tally_id')
                    ->where('transactions.type', 'income'),
                'expense_total' => DB::table('transactions')
                    ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                    ->selectRaw('COALESCE(SUM(transactions.amount / POWER(10, currencies.decimals)), 0)')
                    ->whereColumn('transactions.tally_id', 'tallies.tally_id')
                    ->where('transactions.type', 'expense'),
            ])
            ->get()
            ->map(function (Tally $tally): Tally {
                $tally->income_total = (float) ($tally->income_total ?? 0); // @pest-mutate-ignore
                $tally->expense_total = (float) ($tally->expense_total ?? 0); // @pest-mutate-ignore

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
     */
    public function destroy(DestroyTallyRequest $request, Tally $tally, DestroyTallyAction $action): RedirectResponse
    {
        $action->handle($tally);

        return redirect()->route('tallies.index');
    }
}
