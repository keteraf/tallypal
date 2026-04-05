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
use Illuminate\Support\Facades\URL;

class TallyInvitationExistingUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly TallyInvitation $invitation)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You\'ve been invited to collaborate on :tally', ['tally' => $this->invitation->tally()->firstOrFail()->name]),
        );
    }

    public function content(): Content
    {
        $tally = $this->invitation->tally()->firstOrFail();
        $invitedBy = $this->invitation->invitedBy()->firstOrFail();

        return new Content(
            markdown: 'mail.tally.invitation-existing-user',
            with: [
                'inviterName' => $invitedBy->name,
                'tallyName' => $tally->name,
                'acceptUrl' => URL::signedRoute('invitations.accept', ['uuid' => $this->invitation->uuid]),
                'declineUrl' => URL::signedRoute('invitations.decline', ['uuid' => $this->invitation->uuid]),
            ],
        );
    }
}
