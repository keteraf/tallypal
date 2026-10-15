@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
            <table cellpadding="0" cellspacing="0" border="0" align="center" role="presentation">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        <img src="{{ asset('mail-logo.png') }}" width="28" height="28" alt=""
                             style="display: block; border: 0; border-radius: 9px;">
                    </td>
                    <td style="vertical-align: middle;">
                        <span class="brand-name">{{ $slot }}</span>
                    </td>
                </tr>
            </table>
        </a>
    </td>
</tr>
