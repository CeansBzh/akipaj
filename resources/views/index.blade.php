<x-guest-layout>
    {{-- Hero section --}}
    <section class="relative">
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
                    width="767" height="511">
            </picture>
        </div>
        <div class="relative bg-neutral-900/10">
            <div
                class="mx-auto flex h-[calc(100vh-6rem)] min-h-[27rem] max-w-screen-xl items-center justify-center px-4 py-32 sm:justify-start sm:px-6 lg:px-8">
                <div class="max-w-xl text-center sm:text-left">
                    <div class="text-3xl">
                        <x-application-logo class="mx-auto h-20 sm:mx-0 sm:h-28" />
                        <p class="text-shadow text-white">Bienvenue à bord !</p>
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
        </div>
    </section>

    {{-- Features section --}}
    <section id="animated-svg">
        <div class="mx-auto max-w-screen-xl py-40 px-5 lg:px-12">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold">Fonctionnalités</h2>
                <p>Toute l'actualité et tous vos souvenirs, au même endroit.</p>
            </div>
            <div class="flex flex-col justify-between space-y-8 p-5 sm:flex-row sm:space-x-5 sm:space-y-0">
                <div class="mx-auto max-w-xs text-center sm:m-0 sm:w-1/3">
                    <div class="mx-auto mb-3 w-fit rounded-full bg-sky-200/60 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-12 text-sky-600">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                            </path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </div>
                    <p class="mb-2 text-lg font-semibold">Partage de photos</p>
                    <p class="text-sm">Permettez à tous les membres d'accéder aux souvenirs que vous avez créés</p>
                </div>
                <div class="mx-auto max-w-xs text-center sm:m-0 sm:w-1/3">
                    <div class="mx-auto mb-3 w-fit rounded-full bg-sky-200/60 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-12 text-sky-600">
                            <rect x="3" y="4" width="18" height="18" rx="2"
                                ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <p class="mb-2 text-lg font-semibold">Programme des événements</p>
                    <p class="text-sm">Accédez aux calendrier des sorties prévues de l'association</p>
                </div>
                <div class="mx-auto max-w-xs text-center sm:m-0 sm:w-1/3">
                    <div class="mx-auto mb-3 w-fit rounded-full bg-sky-200/60 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-12 text-sky-600">
                            <polygon points="11 19 2 12 11 5 11 19"></polygon>
                            <polygon points="22 19 13 12 22 5 22 19"></polygon>
                        </svg>
                    </div>
                    <p class="mb-2 text-lg font-semibold">Historique des sorties</p>
                    <p class="text-sm">Remontez le temps en consultant l'historique des sorties passées</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Gallery section --}}
    <section class="bg-white shadow-[0_-2px_35px_5px_rgba(0,0,0,0.2)]">
        <div class="py-24 px-2 lg:mx-auto lg:flex lg:max-w-screen-xl lg:px-12">
            <div class="mb-5 text-center lg:order-last lg:mx-14 lg:mb-52 lg:grow lg:self-center">
                <h2 class="mb-4 text-3xl font-bold">Galerie</h2>
                <p class="mx-auto max-w-md">Vous avez des photos et souhaitez en faire profiter les autres ? La galerie
                    est
                    faite pour ça ✨</p>
                <a href="{{ route('photos.index') }}"
                    class="mt-4 inline-flex items-center rounded-md border border-transparent bg-sky-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    Voir la galerie
                </a>
            </div>
            <div class="relative mx-auto max-w-screen-md columns-3 gap-1 p-4 sm:gap-4 lg:m-0 lg:w-2/3">
                <img class="w-full rounded-md" src="{{ Vite::asset('resources/images/carousel/1.webp') }}"
                    alt="" width="235" height="157" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/2.webp') }}" alt="" width="235"
                    height="352" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/3.webp') }}" alt="" width="235"
                    height="157" loading="lazy" />
                <img class="w-full rounded-md" src="{{ Vite::asset('resources/images/carousel/4.webp') }}"
                    width="235" height="352" loading="lazy" alt="" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/5.webp') }}" alt="" width="235"
                    height="106" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/6.webp') }}" alt="" width="235"
                    height="353" loading="lazy" />
                <img class="w-full rounded-md" src="{{ Vite::asset('resources/images/carousel/7.webp') }}"
                    width="235" height="156" loading="lazy" alt="" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/8.webp') }}" alt="" width="235"
                    height="156" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/9.webp') }}" alt="" width="235"
                    height="176" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/10.webp') }}" alt="" width="235"
                    height="157" loading="lazy" />
                <img class="mt-1 w-full rounded-md sm:mt-4"
                    src="{{ Vite::asset('resources/images/carousel/11.webp') }}" alt="" width="235"
                    height="157" loading="lazy" />
                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white via-white/95"></div>
            </div>
        </div>
    </section>

    {{-- Trips section --}}
    <section id="animated-svg-2">
        <div class="py-40 px-5 lg:mx-auto lg:flex lg:max-w-screen-xl lg:px-12">
            <div class="mb-8 text-center lg:mx-14 lg:grow lg:self-center">
                <h2 class="mb-4 text-3xl font-bold">Historique</h2>
                <p class="mx-auto max-w-md">Pour se remémorer les sorties Akipaj, jusqu'au début en 2001! Albums,
                    membres d'équipage, détails des bateaux,.. ⛵</p>
                <a href="{{ route('trips.index') }}"
                    class="mt-4 inline-flex items-center rounded-md border border-transparent bg-sky-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    Voir l'historique
                </a>
            </div>
            <div class="">
                <img class="mx-auto rounded-md" src="{{ Vite::asset('resources/images/trip.webp') }}" width="590"
                    height="270" loading="lazy" alt="Capture d'écran de la sortie Akipaj Corse 2022" />
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            #animated-svg {
                background-image: url({{ Vite::asset('resources/images/shapes.svg') }});
                background-repeat: no-repeat;
                background-size: cover;
            }

            #animated-svg-2 {
                background-image: url({{ Vite::asset('resources/images/shapes2.svg') }});
                background-repeat: no-repeat;
                background-size: cover;
            }
        </style>
    @endpush

</x-guest-layout>
