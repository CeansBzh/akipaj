<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} – {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#f9fafb">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Nunito:wght@200;300;400;500&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @livewireStyles
    {{-- Livewire and Alpine are bundled by Vite (resources/js/app.js) --}}
    @livewireScriptConfig
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans text-gray-900 antialiased [--coastline-paper:var(--color-gray-50)]">
    <div class="relative isolate flex min-h-dvh flex-col overflow-hidden">
        {{-- Une côte dans le coin en haut à gauche, et sur grand écran son reflet dans le coin opposé --}}
        <x-coastline
            class="text-primary-300 pointer-events-none absolute left-0 top-0 -z-10 size-[34rem] sm:size-[44rem]" />
        <x-coastline
            class="text-primary-300 pointer-events-none absolute bottom-0 right-0 -z-10 hidden size-[44rem] rotate-180 lg:block"
            :soundings="false" />

        <header class="px-4 pt-4 sm:px-6 sm:pt-6">
            <a href="{{ route('home') }}"
                class="focus-visible:outline-primary-700 inline-flex min-h-11 items-center gap-2 rounded-full bg-white/90 px-4 font-semibold text-gray-800 shadow-sm ring-1 ring-gray-200 transition-colors hover:bg-white hover:text-gray-950 focus-visible:outline-2 focus-visible:outline-offset-2">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M19 12H5m6-6-6 6 6 6" />
                </svg>
                Retour à l'accueil
            </a>
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-4 pb-12 pt-6 sm:px-6">
            <div class="w-full max-w-md">
                <div class="flex flex-col items-center text-center">
                    <x-application-logo class="bg-primary-700 h-16 rounded-2xl px-3 py-2.5 shadow-md" />
                    <h1 class="mt-6 text-balance text-2xl font-bold sm:text-3xl">{{ $title }}</h1>
                    @isset($intro)
                        <p class="mt-3 max-w-prose text-pretty leading-relaxed text-gray-700">{{ $intro }}</p>
                    @endisset
                </div>

                <div class="mt-8 rounded-3xl bg-white p-6 shadow-xl shadow-gray-900/5 ring-1 ring-gray-200 sm:p-8">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="mt-6 text-center leading-relaxed text-gray-700">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </main>
    </div>
</body>

</html>
