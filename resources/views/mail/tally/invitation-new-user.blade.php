<x-mail::message>
# You've been invited to join {{ config('app.name') }}

**{{ $inviterName }}** has invited you to collaborate on the tally **"{{ $tallyName }}"** on {{ config('app.name') }}.

To accept this invitation, create your free account — once you register, you'll be automatically added to the tally.

<x-mail::button :url="$registerUrl">
Create Account & Accept
</x-mail::button>

This invitation will expire in 7 days. If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
