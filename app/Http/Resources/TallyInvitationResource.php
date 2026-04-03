<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TallyInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TallyInvitation
 */
class TallyInvitationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'invited_by' => UserResource::make($this->invitedBy),
        ];
    }
}
