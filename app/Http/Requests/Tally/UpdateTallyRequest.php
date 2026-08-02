<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use App\Models\Currency;
use App\Models\Tally;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTallyRequest extends FormRequest
{
    use ResolvesDefaultCurrency;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('tally'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string'],
            'color' => ['nullable', 'string', Rule::in(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])],
            'glyph' => ['nullable', 'string', Rule::in([...Tally::GLYPHS, $this->currentGlyph()])],
            'default_currency_uuid' => ['nullable', 'uuid', 'exists:' . Currency::TABLE_NAME . ',uuid'],
        ];
    }

    /**
     * The tally's stored glyph, which the edit form re-submits unchanged even when it predates the picker set.
     */
    private function currentGlyph(): ?string
    {
        $tally = $this->route('tally');

        return $tally instanceof Tally ? $tally->glyph : null;
    }
}
