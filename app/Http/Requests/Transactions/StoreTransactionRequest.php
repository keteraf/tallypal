<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', [Transaction::class, $this->route('tally')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'party_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,category_id',
            'currency_id' => 'required|exists:currencies,currency_id',
            'type' => 'required|in:income,expense',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            /** @var string|int|float $amount */
            $amount = $this->input('amount');

            if (! is_numeric($amount)) {
                $amount = str_replace(',', '.', (string) $amount); // @pest-mutate-ignore
            }

            if (is_numeric($amount)) {
                $this->merge(['amount' => (float) $amount]);
            }
        }
    }
}
