<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use App\Models\Currency;
use App\Models\Tally;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTallyRequest extends FormRequest
{
    use ResolvesDefaultCurrency;

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', Rule::in(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])],
            'glyph' => ['nullable', 'string', Rule::in(Tally::GLYPHS)],
            'default_currency_uuid' => ['nullable', 'uuid', 'exists:' . Currency::TABLE_NAME . ',uuid'],
        ];
    }
}
