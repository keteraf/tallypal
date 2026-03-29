<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
                overflow:  hidden;
            }

            html.dark {
                background-color: oklch(0.145 0 0);
                overflow:  hidden;
                color-scheme: dark;
            }

            .beta-ribbon-layer {
                position: fixed;
                inset: 0;
                z-index: 9999;
                overflow: hidden;
                pointer-events: none;
                user-select: none;
            }

            .beta-ribbon {
                position: absolute;
                top: 1rem;
                right: -2.1rem;
                width: 8rem;
                padding: 0.35rem 0;
                transform: rotate(45deg);
                transform-origin: center;
                text-align: center;
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                color: oklch(0.98 0.02 95);
                background: oklch(0.62 0.18 45);
                box-shadow: 0 8px 20px oklch(0 0 0 / 0.28);
            }

            @media (max-width: 640px) {
                .beta-ribbon {
                    width: 6.5rem;
                    top: 0.9rem;
                    right: -1.75rem;
                    font-size: 0.58rem;
                    padding: 0.3rem 0;
                }
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet">

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div class="beta-ribbon-layer" aria-hidden="true">
            <div class="beta-ribbon">BETA</div>
        </div>
        @inertia
    </body>
</html>
