<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('delete', $this->route('transaction'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
