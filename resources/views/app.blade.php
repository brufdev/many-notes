<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">
    <meta name="apple-mobile-web-app-title" content="Many Notes">
    <title>{{ $title ?? 'Many Notes' }}</title>
    <link rel="icon" type="image/png" href="/assets/icon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/assets/icon-16x16.png" sizes="16x16">
    <link rel="shortcut icon" href="/assets/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/assets/icon.svg">
    <link rel="apple-touch-icon" href="/assets/icon-180x180.png" sizes="180x180">
    <link rel="manifest" href="/build/manifest.webmanifest">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body>
    @inertia
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/build/sw.js');
        });
    }
    </script>
</body>
</html>
