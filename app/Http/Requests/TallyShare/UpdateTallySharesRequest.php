<?php

declare(strict_types=1);

namespace App\Http\Requests\TallyShare;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTallySharesRequest extends FormRequest
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
            'shares' => ['required', 'array'],
            'shares.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'shares.*.percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var array<int, array{user_id: int, percentage: float|int}> $shares */
            $shares = $this->input('shares');
            $total = collect($shares)->sum('percentage');

            /** @var float|int $total */
            if ((float) $total !== 100.0) {
                $validator->errors()->add('shares', 'The total percentage must equal 100.');
            }

            /** @var \App\Models\Tally $tally */
            $tally = $this->route('tally');
            $currentUserIds = $tally->users()->pluck('user_id')->sort()->values();
            $submittedUserIds = collect($shares)->pluck('user_id')->sort()->values();

            if (! $currentUserIds->diff($submittedUserIds)->isEmpty()) {
                $validator->errors()->add('shares', 'All current collaborators must be included in the update.');
            }
        });
    }
}
