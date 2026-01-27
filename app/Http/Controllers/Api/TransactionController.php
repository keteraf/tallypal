<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Transaction\DestroyTransactionAction;
use App\Actions\Transaction\StoreTransactionAction;
use App\Actions\Transaction\UpdateTransactionAction;
use App\DTOs\TransactionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\DestroyTransactionRequest;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Tally;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Tally $tally): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Transaction::class, $tally]);

        $transactions = $tally->transactions()
            ->latest()
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request, Tally $tally, StoreTransactionAction $action): TransactionResource
    {
        /** @var array{party_id: int|numeric-string, currency_id: int|numeric-string, category_id: int|numeric-string, type: string, title: string, description?: string|null, amount: float|numeric-string, transaction_date: string} $data */
        $data = $request->validated();
        $transactionDTO = TransactionDTO::fromArray($data);
        $transaction = $action->handle($transactionDTO, $tally);

        return new TransactionResource($transaction);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tally $tally, Transaction $transaction): TransactionResource
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Tally $tally, Transaction $transaction, UpdateTransactionAction $action): TransactionResource
    {
        /** @var array{party_id: int|numeric-string, currency_id: int|numeric-string, category_id: int|numeric-string, type: string, title: string, description?: string|null, amount: float|numeric-string, transaction_date: string} $data */
        $data = $request->validated();
        $transactionDTO = TransactionDTO::fromArray($data);
        $transaction = $action->handle($transaction, $transactionDTO);

        return new TransactionResource($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTransactionRequest $request, Tally $tally, Transaction $transaction, DestroyTransactionAction $action): JsonResponse
    {
        $action->handle($transaction);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
