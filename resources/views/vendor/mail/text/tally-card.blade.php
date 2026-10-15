@props(['tally', 'inviter'])
{{ $tally }}
{{ __('Invited by :name', ['name' => $inviter]) }}
