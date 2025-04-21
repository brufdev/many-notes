<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">
    <title>{{ $title ?? 'Many Notes' }}</title>
    <link rel="icon" type="image/png" href="/assets/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="/assets/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon.ico" />
    <link rel="apple-touch-icon" href="/assets/apple-touch-icon.png" sizes="180x180" />
    <meta name="apple-mobile-web-app-title" content="Many Notes" />
    <link rel="manifest" href="/assets/site.webmanifest" />

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="text-light-base-950 dark:text-base-50">
    {{ $slot }}
</body>

</html>
