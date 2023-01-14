<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Site internet de l'association de voile Akipaj.">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Nunito:wght@200;300;400;500&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
    @livewireScripts
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div class="fixed inset-x-3 top-5 max-w-xl mx-auto z-50">
        @foreach (App\Enum\AlertLevelEnum::cases() as $alertLevel)
        @if(Session::has('alert-' . $alertLevel->name))
        <x-alert :message="Session::get('alert-' . $alertLevel->name)" :level="$alertLevel" />
        @endif
        @endforeach
    </div>
    <div class="min-h-screen bg-gray-100">
        {{ $slot }}
    </div>

    @stack('scripts')
</body>

</html>
