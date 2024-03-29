<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Nunito:wght@200;300;400;500&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        #wave-svg {
            background-image: url({{ Vite::asset('resources/images/waves.svg') }});
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="bg-gray-100/50">
    <div class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </div>

    <footer id="wave-svg" class="flex h-64 items-end justify-between p-4 text-white md:p-6">
        <span class="text-sm sm:text-center">{{ date('Y') }} Akipaj</span>
        <ul class="mt-3 flex flex-wrap items-center sm:mt-0">
            <li>
                <a href="{{ route('legal') }}" class="mr-4 text-sm hover:underline md:mr-6">Mentions
                    légales</a>
            </li>
        </ul>
    </footer>

    @stack('scripts')
</body>

</html>
