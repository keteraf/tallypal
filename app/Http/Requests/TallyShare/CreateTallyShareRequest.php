<?php

declare(strict_types=1);

namespace App\Http\Requests\TallyShare;

use Illuminate\Foundation\Http\FormRequest;

class CreateTallyShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Tally $tally */
        $tally = $this->route('tally');

        return $this->user()?->getKey() === $tally->owner_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }
}
