<x-guest-layout>
    {{-- Hero section --}}
    <section class="relative mb-10">
        <div class="absolute inset-0">
            <picture>
                {{-- XL (1536px to infinite) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.avif') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.webp') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.jpg') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/jpeg">
                {{-- Large (1024px to 1535px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.avif') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.webp') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.jpg') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/jpeg">
                {{-- Medium (768px to 1023px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.avif') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.webp') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.jpg') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/jpeg">
                {{-- Small (to 767px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero.avif') }}" media="(max-width: 767px)"
                    width="767" height="511" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero.webp') }}" media="(max-width: 767px)"
                    width="767" height="511" type="image/webp">
                {{-- Fallback image = small JPG --}}
                <img class="h-full w-full object-cover" src="{{ Vite::asset('resources/images/hero/hero.jpg') }}"
                    alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                    width="767" height="511" loading="lazy" decoding="async">
            </picture>
        </div>
        <div
            class="relative mx-auto flex h-96 max-w-screen-xl items-center justify-center px-4 py-32 sm:justify-start sm:px-6 lg:px-8">
            <div class="max-w-xl text-center sm:text-left">
                <div class="text-3xl">
                    <a href="{{ url('/') }}" class="text-yellow-500 hover:underline">
                        <h1 class="text-shadow block font-bold shadow-gray-500 sm:text-5xl">
                            Akipaj
                        </h1>
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap gap-4 text-center">
                    <a href="{{ route('register') }}"
                        class="block w-full rounded bg-sky-600 px-12 py-3 font-medium text-white shadow hover:bg-sky-500 focus:outline-none focus:ring active:bg-sky-600 sm:w-auto">
                        Créer un compte
                    </a>
                    <a href="{{ route('login') }}"
                        class="block w-full rounded bg-white px-12 py-3 font-medium text-sky-900 shadow hover:text-sky-500 focus:outline-none focus:ring active:text-sky-400 sm:w-auto">
                        Je suis déjà membre
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-lg">
        <h2 class="mb-5 font-sans text-2xl">Mentions légales</h2>
        <div class="mb-5">
            <p class="mb-2 font-semibold">Informations sur l'association Akipaj :</p>
            <ul class="mb-4">
                <li>Nom de l'association : Akipaj</li>
                <li>Adresse : 2 La Noé, 35410 Nouvoitou, France</li>
                <li>N° de téléphone : 02 99 62 97 91</li>
                <li>Le site Akipaj est publié par Laurent Briantais.</li>
            </ul>
            <p class="mb-2 font-semibold">Informations sur l'hébergement du site :</p>
            <ul class="mb-3">
                <li>Site hébergé par : OVH</li>
                <li>Adresse du siège social : 2 rue Kellerman, 59100 Roubaix, France</li>
            </ul>
        </div>
    </section>

</x-guest-layout>
