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
                background-color: oklch(0.975 0.006 265);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        {{-- TODO: self-host these fonts for better performance (eliminate render-blocking third-party requests) --}}
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        <meta name="description" content="{{ __('seo.default_description') }}" inertia>
        <meta name="theme-color" content="#F8F6F1">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <meta property="og:site_name" content="{{ config('app.name') }}" inertia>
        <meta property="og:type" content="website" inertia>
        <meta property="og:locale" content="it_IT" inertia>
        <meta property="og:url" content="{{ url()->current() }}" inertia>
        <meta property="og:image" content="{{ asset('og/default.png') }}" inertia>
        <meta name="twitter:card" content="summary_large_image" inertia>
        <meta name="twitter:image" content="{{ asset('og/default.png') }}" inertia>
        <link rel="canonical" href="{{ url()->current() }}" inertia>

        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => config('app.url') . '/#organization',
                    'name' => config('app.name'),
                    'url' => config('app.url'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => url('/favicon.svg'),
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => config('app.url') . '/#website',
                    'name' => config('app.name'),
                    'url' => config('app.url'),
                    'publisher' => [
                        '@id' => config('app.url') . '/#organization',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
