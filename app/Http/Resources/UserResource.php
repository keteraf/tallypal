<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TallyUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read TallyUser $pivot
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'percentage' => $this->whenPivotLoaded('tally_user', fn () => $this->pivot->default_share_percentage),
        ];
    }
}
