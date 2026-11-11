<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    {{-- Un lien de partage reste entre ceux qui l'ont reçu : ni moteur de recherche, ni jeton transmis à d'autres sites --}}
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">

    <title>{{ $title }} – {{ config('app.name', 'Laravel') }}</title>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="Partagé par le club de voile Akipaj.">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="{{ $dark ? '#000000' : '#f9fafb' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Nunito:wght@200;300;400;500&display=swap"
        rel="stylesheet">

    {{-- Ni Livewire ni Alpine : la visionneuse des photos partagées suffit --}}
    @vite(['resources/css/app.css', 'resources/js/shared-photos.js'])
</head>

<body @class([
    'font-sans antialiased',
    'bg-black text-white' => $dark,
    'bg-gray-50 text-gray-900' => !$dark,
])>
    {{ $slot }}
</body>

</html>
