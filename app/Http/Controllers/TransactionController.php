<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Actions\Transaction\GetTallyDebtsAction;
use App\Actions\Transaction\GetTallyPerPersonAction;
use App\Actions\Transaction\GetTallySplitAction;
use App\Actions\Transaction\GetTallyTotalsAction;
use App\Actions\Transaction\GetTallyTransactionsAction;
use App\Actions\Transaction\StoreTransactionAction;
use App\Actions\Transaction\UpdateTransactionAction;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\DTOs\TransactionDTO;
use App\Enums\CategoryType;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly TransactionStatsRepositoryInterface $statsRepository) {}

    /**
     * Display a listing of the transactions for a tally.
     */
    public function index(
        Request $request,
        Tally $tally,
        GetTallyTransactionsAction $getTransactionsAction,
        GetTallyTotalsAction $getTotalsAction,
        GetTallyDebtsAction $getDebtsAction,
        GetTallySplitAction $getSplitAction,
        GetTallyPerPersonAction $getPerPersonAction,
        GetBalanceSummaryAction $getBalanceSummaryAction
    ): Response {
        $this->authorize('view', $tally);

        return Inertia::render('tallies/transactions/Index', [
            'tally' => [
                ...$tally->only(['tally_id', 'uuid', 'name', 'description', 'owner_id', 'color', 'glyph']),
                'users_count' => $tally->membersCount(),
            ],
            'transactions' => Inertia::scroll(function () use ($getTransactionsAction, $tally) {
                /** @var \Illuminate\Pagination\LengthAwarePaginator<int, Transaction> $paginator */
                $paginator = $getTransactionsAction->handle($tally);

                return $paginator->through(fn (Transaction $t): array => [
                    'transaction_id' => $t->transaction_id,
                    'uuid' => $t->uuid,
                    'title' => $t->title,
                    'amount' => $t->amount,
                    'type' => $t->type->value,
                    'transaction_date' => $t->transaction_date,
                    'category' => $t->category ? ['category_id' => $t->category->category_id, 'name' => $t->category->name] : null,
                    'currency' => $t->currency ? ['symbol' => $t->currency->symbol, 'decimals' => $t->currency->decimals] : null,
                    'party' => $t->party ? ['name' => $t->party->name, 'initials' => $t->party->initials(), 'color' => $t->party->avatarColor()] : null,
                ]);
            }),
            'totals' => Inertia::merge(fn (): \Illuminate\Support\Collection => $getTotalsAction->handle($tally)),
            'per_person' => Inertia::merge(fn (): \Illuminate\Support\Collection => $getPerPersonAction->handle($tally)),
            'debts' => Inertia::merge(fn (): \Illuminate\Support\Collection => $getDebtsAction->handle($tally)),
            'settled_members' => Inertia::merge(fn (): \Illuminate\Support\Collection => $getBalanceSummaryAction->settledMembers($tally)),
            'splits' => Inertia::merge(fn (): \Illuminate\Support\Collection => $getSplitAction->handle($tally)),
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Tally $tally): Response
    {
        $this->authorize('view', $tally);

        return Inertia::render('tallies/transactions/Create', [
            'tally' => $tally->only(['tally_id', 'uuid', 'name']),
            ...$this->transactionFormData($tally),
        ]);
    }

    /**
     * Return categories, currencies and parties for a tally's transaction form as JSON.
     */
    public function formData(Tally $tally): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $tally);

        return response()->json($this->transactionFormData($tally));
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(StoreTransactionRequest $request, Tally $tally, StoreTransactionAction $action): RedirectResponse
    {
        /** @var array{party_id: int|numeric-string, currency_id: int|numeric-string, category_id?: int|numeric-string|null, type: string, title: string, description?: string|null, amount: float|numeric-string, transaction_date: string} $data */
        $data = $request->validated();
        $transactionDTO = TransactionDTO::fromArray($data);
        $action->handle($transactionDTO, $tally);

        return redirect()->route('tallies.transactions.index', $tally);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Tally $tally, Transaction $transaction): Response
    {
        $this->authorize('view', $transaction);

        return Inertia::render('tallies/transactions/Edit', [
            'tally' => $tally->only(['tally_id', 'uuid', 'name']),
            'transaction' => $transaction->only(['transaction_id', 'uuid', 'title', 'amount', 'party_id', 'category_id', 'currency_id', 'type', 'transaction_date', 'description']),
            ...$this->transactionFormData($tally),
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(
        UpdateTransactionRequest $request,
        Tally $tally,
        Transaction $transaction,
        UpdateTransactionAction $action
    ): RedirectResponse {
        $this->authorize('update', $transaction);

        /** @var array{party_id: int|numeric-string, currency_id: int|numeric-string, category_id?: int|numeric-string|null, type: string, title: string, description?: string|null, amount: float|numeric-string, transaction_date: string} $data */
        $data = $request->validated();
        $transactionDTO = TransactionDTO::fromArray($data);
        $action->handle($transaction, $transactionDTO);

        return redirect()->route('tallies.transactions.index', $tally);
    }

    /**
     * Build the shared categories / currencies / parties payload for the transaction form.
     *
     * @return array{frequent_categories: array{expense: list<string>, income: list<string>}, categories: \Illuminate\Database\Eloquent\Collection<int, Category>, currencies: \Illuminate\Database\Eloquent\Collection<int, Currency>, default_currency_uuid: string|null, parties: \Illuminate\Support\Collection<int, array{id: int, name: string}>}
     */
    private function transactionFormData(Tally $tally): array
    {
        $userId = (int) auth()->id();
        $now = CarbonImmutable::now();

        return [
            'frequent_categories' => [
                'expense' => $this->statsRepository->frequentCategories($userId, CategoryType::EXPENSE, $now),
                'income' => $this->statsRepository->frequentCategories($userId, CategoryType::INCOME, $now),
            ],
            'categories' => Category::query()->orderBy(Category::PRIMARY_KEY)->get([Category::PRIMARY_KEY, 'slug', 'type', 'name', 'color']),
            'currencies' => Currency::all(['currency_id', 'uuid', 'name', 'code', 'symbol']),
            'default_currency_uuid' => $tally->prefillCurrency(auth()->user())?->uuid,
            'parties' => $tally->users()->get()->map(fn (User $u): array => ['id' => $u->id, 'name' => $u->name]),
        ];
    }
}
