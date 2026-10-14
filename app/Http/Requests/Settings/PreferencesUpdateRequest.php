<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;

final class PreferencesUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'default_currency_uuid' => ['nullable', 'uuid', 'exists:' . Currency::TABLE_NAME . ',uuid'],
        ];
    }
}
