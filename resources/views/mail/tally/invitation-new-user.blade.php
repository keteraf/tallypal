<x-mail::message>
<p class="eyebrow">Invitation</p>

# You're invited to {{ config('app.name') }}

**{{ $inviterName }}** wants to track shared expenses with you. Create your free account and you'll be added to the tally automatically.

<x-mail::tally-card :tally="$tallyName" :inviter="$inviterName" />

<x-mail::button :url="$registerUrl">
Create account and join
</x-mail::button>

<p class="sub">This invitation expires in 7 days. Not expecting it? You can ignore this email.</p>
</x-mail::message>
