<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TallyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class TallyInvitationNewUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly TallyInvitation $invitation)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You\'ve been invited to join :app and collaborate on :tally', [
                'app' => Config::string('app.name'),
                'tally' => $this->invitation->tally()->firstOrFail()->name,
            ]),
        );
    }

    public function content(): Content
    {
        $tally = $this->invitation->tally()->firstOrFail();
        $invitedBy = $this->invitation->invitedBy()->firstOrFail();

        return new Content(
            markdown: 'mail.tally.invitation-new-user',
            with: [
                'inviterName' => $invitedBy->name,
                'tallyName' => $tally->name,
                'registerUrl' => route('register', ['invitation' => $this->invitation->token]),
            ],
        );
    }
}
