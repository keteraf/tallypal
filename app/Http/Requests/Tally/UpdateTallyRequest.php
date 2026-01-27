<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTallyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('tally'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:25'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
