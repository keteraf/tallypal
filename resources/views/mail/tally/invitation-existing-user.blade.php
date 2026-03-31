<x-mail::message>
# You've been invited to collaborate

**{{ $inviterName }}** has invited you to collaborate on the tally **"{{ $tallyName }}"** on {{ config('app.name') }}.

Accept the invitation to start tracking shared expenses together.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

If you don't want to join this tally, you can decline:

<x-mail::button :url="$declineUrl" color="red">
Decline Invitation
</x-mail::button>

This invitation will expire in 7 days. If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
