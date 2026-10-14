<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use App\Models\Tally;
use App\Models\Transaction;
use App\Rules\CategoryMatchesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $tally = $this->route('tally');
        $tallyId = $tally instanceof Tally ? $tally->tally_id : null;

        return [
            'party_id' => ['required', Rule::exists('tally_user', 'user_id')->where('tally_id', $tallyId)],
            'category_id' => ['nullable', 'exists:categories,category_id', new CategoryMatchesType()],
            'currency_id' => 'required|exists:currencies,currency_id',
            'type' => 'required|in:income,expense',
            'title' => 'required|string|max:25',
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
