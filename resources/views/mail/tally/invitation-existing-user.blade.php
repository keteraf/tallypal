<x-mail::message>
<p class="eyebrow">Invitation</p>

# Join “{{ $tallyName }}”

**{{ $inviterName }}** wants to track shared expenses with you on {{ config('app.name') }}.

<x-mail::tally-card :tally="$tallyName" :inviter="$inviterName" />

<x-mail::button :url="$acceptUrl">
Accept invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="secondary">
Decline
</x-mail::button>

<p class="sub">This invitation expires in 7 days. Not expecting it? You can ignore this email.</p>
</x-mail::message>
