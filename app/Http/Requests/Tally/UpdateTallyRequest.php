<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use App\Models\Tally;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTallyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Tally|null $tally */
        $tally = $this->route('tally');
        $user = auth()->user();

        if (! $tally || ! $user) {
            return false;
        }

        return $tally->owner_id === $user->id ||
            $tally->users()->where('user_id', $user->id)->exists();
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
