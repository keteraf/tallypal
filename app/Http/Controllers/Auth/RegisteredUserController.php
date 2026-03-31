<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\TallyShare\AcceptTallyInvitationAction;
use App\Http\Controllers\Controller;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register', []);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->acceptPendingInvitations($user);

        return to_route('dashboard');
    }

    private function acceptPendingInvitations(User $user): void
    {
        $action = app(AcceptTallyInvitationAction::class);

        TallyInvitation::query()
            ->with('tally')
            ->where('email', mb_strtolower($user->email))
            ->pending()
            ->notExpired()
            ->each(function (TallyInvitation $invitation) use ($user, $action): void {
                $action->handle($invitation, $user);
            });
    }
}
