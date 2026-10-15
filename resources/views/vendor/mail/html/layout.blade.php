<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@500&display=swap"
        rel="stylesheet">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        /* Dark values of the DESIGN_TOKENS.md §1 colours (the light ones live in themes/default.css). */
        @media (prefers-color-scheme: dark) {
            body,
            .wrapper,
            .body {
                background-color: #080c14 !important;
                color: #f5f7fa !important;
            }

            .inner-body {
                background-color: #131720 !important;
                border-color: #262b36 !important;
            }

            h1,
            h2,
            h3,
            strong,
            .header a,
            .brand-name,
            .tally-card-name {
                color: #f5f7fa !important;
            }

            p,
            li,
            .panel-content,
            .panel-content p,
            .table td {
                color: #b4b7bf !important;
            }

            p.sub,
            .eyebrow,
            .subcopy p,
            .footer p,
            .footer a,
            .tally-card-meta {
                color: #7c808a !important;
            }

            a {
                color: #629bff !important;
            }

            .subcopy,
            .table th {
                border-color: #262b36 !important;
            }

            .panel-content,
            .tally-card {
                background-color: #1e232e !important;
            }

            .button-primary,
            .button-success,
            .button-blue,
            .button-green,
            .tally-card-glyph {
                background-color: #629bff !important;
                border-color: #629bff !important;
                color: #090d16 !important;
            }

            .button-secondary {
                background-color: #1e232e !important;
                border-color: #262b36 !important;
                color: #f5f7fa !important;
            }

            .button-error,
            .button-red {
                background-color: #ff696d !important;
                border-color: #ff696d !important;
                color: #090d16 !important;
            }
        }
    </style>
    {!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                {!! $header ?? '' !!}

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               role="presentation">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                    {!! $subcopy ?? '' !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {!! $footer ?? '' !!}
            </table>
        </td>
    </tr>
</table>
</body>
</html>
