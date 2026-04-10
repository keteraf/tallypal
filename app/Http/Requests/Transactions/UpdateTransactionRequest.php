<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use App\Models\Tally;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('transaction'));
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
            'category_id' => 'required|exists:categories,category_id',
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
            $amount = $this->input('amount');

            if (! is_scalar($amount) && ! $amount instanceof Stringable) {
                return;
            }

            $normalizedAmount = str_replace(',', '.', (string) $amount); // @pest-mutate-ignore

            if (is_numeric($normalizedAmount)) {
                $this->merge(['amount' => (float) $normalizedAmount]);
            }
        }
    }
}
