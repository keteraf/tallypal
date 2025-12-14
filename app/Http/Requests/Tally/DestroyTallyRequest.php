<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use App\Models\Tally;
use Illuminate\Foundation\Http\FormRequest;

class DestroyTallyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Tally|null $tally */
        $tally = $this->route('tally');

        if (! $tally || ($tally->owner_id !== auth()->user()?->id)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
