<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTallyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('delete', $this->route('tally'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
