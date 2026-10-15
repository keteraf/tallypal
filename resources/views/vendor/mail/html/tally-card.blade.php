@props(['tally', 'inviter'])
<table class="tally-card" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td style="padding: 16px;">
            <table cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="tally-card-glyph" width="36" height="36" align="center" valign="middle">
                        {{ mb_strtoupper(mb_substr($tally, 0, 1)) }}
                    </td>
                    <td style="padding-left: 12px;" valign="middle">
                        <p class="tally-card-name">{{ $tally }}</p>
                        <p class="tally-card-meta">{{ __('Invited by :name', ['name' => $inviter]) }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
