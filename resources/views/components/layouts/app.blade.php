<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">

    <title>{{ $title ?? 'Many Notes' }}</title>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
    {{ $slot }}
</body>

</html>
