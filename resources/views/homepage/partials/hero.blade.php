<section class="relative">
    <div class="absolute inset-0 hidden overflow-hidden md:block">
        <picture>
            {{-- XL (1536px to infinite) --}}
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-xl.avif') }}" media="(min-width: 1536px)"
                width="2600" height="700" type="image/avif">
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-xl.webp') }}" media="(min-width: 1536px)"
                width="2600" height="700" type="image/webp">
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-xl.jpg') }}"
                media="(min-width: 1536px)" width="2600" height="700" type="image/jpeg">
            {{-- Large (1024px to 1535px) --}}
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-lg.avif') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/avif">
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-lg.webp') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/webp">
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-lg.jpg') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/jpeg">
            {{-- Medium (768px to 1023px) --}}
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-md.avif') }}"
                media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="575" type="image/avif">
            <source srcset="{{ Vite::asset('resources/views/homepage/images/hero-md.webp') }}"
                media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="575" type="image/webp">
            {{-- Fallback image = medium JPG --}}
            <img class="h-full w-full object-cover"
                src="{{ Vite::asset('resources/views/homepage/images/hero-md.jpg') }}"
                alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                width="1023" height="575" loading="lazy" decoding="async">
        </picture>
    </div>
    <div class="relative md:bg-neutral-900/20">
        <div class="relative mx-auto min-h-[27rem] max-w-screen-2xl md:flex">
            {{-- Left/Top side --}}
            <div class="relative md:flex-grow">
                <div class="absolute inset-0 overflow-hidden md:hidden">
                    <picture>
                        <source srcset="{{ Vite::asset('resources/views/homepage/images/hero.avif') }}"
                            type="image/avif">
                        <source srcset="{{ Vite::asset('resources/views/homepage/images/hero.webp') }}"
                            type="image/webp">
                        <img class="h-full w-full object-cover"
                            src="{{ Vite::asset('resources/views/homepage/images/hero.jpg') }}"
                            alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                            width="767" height="511" loading="lazy" decoding="async">
                    </picture>
                </div>
                <div class="relative mx-auto h-full min-h-[21rem] max-w-screen-xl bg-neutral-900/20 md:bg-transparent">
                    <div class="pt-12 text-center md:ml-12 md:pt-40 md:text-left lg:ml-24">
                        <div class="mx-auto w-fit md:m-0">
                            <x-application-logo class="mx-auto mb-3 h-20 md:mx-0 md:h-28" />
                            <p class="text-shadow mb-3 text-2xl text-white shadow-gray-800">Bienvenue à bord !</p>
                            <a href="{{ route('profile.index') }}"
                                class="text-shadow-outline inline-flex items-center rounded border border-gray-100 px-4 py-2 text-sm font-semibold tracking-widest text-gray-100 shadow-sm shadow-gray-800 transition duration-150 ease-in-out hover:border-white hover:bg-gray-100/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25">
                                Mon profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Events section --}}
            <div class="relative mb-5 w-full px-2 md:m-0 md:w-fit md:p-6">
                <div class="mx-auto -mt-24 max-w-sm rounded bg-white p-5 shadow-lg md:mt-0">
                    <h2 class="text-2xl font-extrabold">Prochaines sorties</h2>
                    <hr class="my-5">
                    <ul class="list-disc px-5">
                        @forelse(\App\Models\Event::where('start_time', '>=',
                    date('Y-m-d'))->orderBy('start_time')->take(5)->get() as $event)
                            <li class="mt-2 font-semibold">
                                <a href="{{ route('events.show', $event) }}">
                                    {{ $event->name }}
                                </a>
                                <p class="text-sky-600">{{ $event->start_time->translatedFormat('d M Y') }}</p>
                            </li>
                        @empty
                            <li class="flex flex-col items-center justify-center">
                                <p class="text-center text-gray-500">Aucun événement à venir</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
