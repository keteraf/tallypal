<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TallyInvitationResource;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TallyInvitationController extends Controller
{
    public function index(Request $request, Tally $tally): AnonymousResourceCollection
    {
        $this->authorize('view', $tally);

        $invitations = $tally->invitations()
            ->with('invitedBy')
            ->pending()
            ->notExpired()
            ->get();

        return TallyInvitationResource::collection($invitations);
    }

    public function destroy(Request $request, Tally $tally, TallyInvitation $invitation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->getKey() === $tally->owner_id, Response::HTTP_FORBIDDEN);
        abort_unless($invitation->tally_id === $tally->getKey(), Response::HTTP_NOT_FOUND);
        abort_unless($invitation->isPending(), Response::HTTP_UNPROCESSABLE_ENTITY);

        $invitation->update(['status' => TallyInvitation::STATUS_DECLINED]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
