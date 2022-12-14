<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Scripts -->
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
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

    @livewireScripts
    @stack('scripts')
</body>

</html>
