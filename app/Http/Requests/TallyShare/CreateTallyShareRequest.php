<?php

declare(strict_types=1);

namespace App\Http\Requests\TallyShare;

use App\Models\TallyInvitation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'email' => ['required', 'email'],
        ];
    }

    /** @return array<int, Closure> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var \App\Models\Tally $tally */
                $tally = $this->route('tally');
                $email = $this->string('email')->lower()->toString();

                $alreadyCollaborator = $tally->users()->where('email', $email)->exists();
                if ($alreadyCollaborator) {
                    $validator->errors()->add('email', __('This user is already a collaborator on this tally.'));

                    return;
                }

                $pendingInvitationExists = $tally->invitations()
                    ->where('email', $email)
                    ->where('status', TallyInvitation::STATUS_PENDING)
                    ->notExpired()
                    ->exists();

                if ($pendingInvitationExists) {
                    $validator->errors()->add('email', __('An invitation has already been sent to this email.'));
                }
            },
        ];
    }
}
