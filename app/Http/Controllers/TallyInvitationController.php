<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\TallyShare\AcceptTallyInvitationAction;
use App\Models\TallyInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TallyInvitationController extends Controller
{
    public function accept(Request $request, string $uuid, AcceptTallyInvitationAction $action): RedirectResponse
    {
        $invitation = TallyInvitation::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (! $invitation->isPending()) {
            return to_route('tallies.index')
                ->with('error', __('This invitation has already been responded to.'));
        }

        if ($invitation->isExpired()) {
            return to_route('tallies.index')
                ->with('error', __('This invitation has expired.'));
        }

        if (! Auth::check()) {
            session()->put('url.intended', $request->fullUrl());

            return to_route('login');
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            return to_route('tallies.index')
                ->with('error', __('This invitation was sent to a different email address.'));
        }

        $action->handle($invitation, $user);

        return to_route('tallies.index')
            ->with('success', __('You have joined the tally ":tally".', ['tally' => $invitation->tally()->firstOrFail()->name]));
    }

    public function decline(Request $request, string $uuid): RedirectResponse
    {
        $invitation = TallyInvitation::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (! $invitation->isPending()) {
            return to_route('home')
                ->with('error', __('This invitation has already been responded to.'));
        }

        $invitation->update(['status' => TallyInvitation::STATUS_DECLINED]);

        return to_route('home')
            ->with('success', __('You have declined the invitation.'));
    }
}
