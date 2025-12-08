<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|string>
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
            $amount = $this->input('amount');

            if (is_numeric($amount)) {
                $amountString = (string) $amount;
                $this->merge(['amount' => (float) str_replace(',', '.', $amountString)]);
            }
        }
    }
}
