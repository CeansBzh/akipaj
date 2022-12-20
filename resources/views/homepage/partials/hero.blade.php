<section class="relative">
    <div class="absolute inset-0 overflow-hidden hidden md:block">
        <picture>
            {{-- XL (1536px to infinite) --}}
            <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.avif') }}" media="(min-width: 1536px)"
                width="2600" height="700" type="image/avif">
            <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.webp') }}" media="(min-width: 1536px)"
                width="2600" height="700" type="image/webp">
            <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.jpg') }}" media="(min-width: 1536px)"
                width="2600" height="700" type="image/jpeg">
            {{-- Large (1024px to 1535px) --}}
            <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.avif') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/avif">
            <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.webp') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/webp">
            <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.jpg') }}"
                media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="559" type="image/jpeg">
            {{-- Medium (768px to 1023px) --}}
            <source srcset="{{ Vite::asset('resources/images/hero/hero-md.avif') }}"
                media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="575" type="image/avif">
            <source srcset="{{ Vite::asset('resources/images/hero/hero-md.webp') }}"
                media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="575" type="image/webp">
            {{-- Fallback image = medium JPG --}}
            <img class="w-full h-full object-cover" src="{{ Vite::asset('resources/images/hero/hero-md.jpg') }}"
                alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                width="1023" height="575" loading="lazy" decoding="async">
        </picture>
    </div>
    <div class="relative max-w-screen-2xl mx-auto min-h-[27rem] md:flex">
        {{-- Left/Top side --}}
        <div class="relative md:flex-grow">
            <div class="absolute inset-0 overflow-hidden md:hidden">
                <picture>
                    <source srcset="{{ Vite::asset('resources/images/hero/hero.avif') }}" type="image/avif">
                    <source srcset="{{ Vite::asset('resources/images/hero/hero.webp') }}" type="image/webp">
                    <img class="w-full h-full object-cover" src="{{ Vite::asset('resources/images/hero/hero.jpg') }}"
                        alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                        width="767" height="511" loading="lazy" decoding="async">
                </picture>
            </div>
            <div class="relative mx-auto max-w-screen-xl min-h-[20rem] h-full">
                <div class="text-center pt-12 md:text-left md:ml-12 md:pt-40 lg:ml-24">
                    <h1 class="text-7xl text-white mb-2 shadow-gray-800 text-shadow">Akipaj</h1>
                    <p class="text-xl text-amber-300 mb-3 shadow-gray-800 text-shadow-outline">Bienvenue à bord !</p>
                    <a href="{{ route('profile.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-100 text-gray-100 shadow-gray-800 text-shadow-outline rounded
                font-semibold text-sm tracking-widest shadow-sm hover:border-white hover:text-white hover:bg-gray-100/10 focus:outline-none
                focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Mon profil
                    </a>
                </div>
            </div>
        </div>

        {{-- Events section --}}
        <div class="relative w-full px-2 mb-5 md:w-fit md:p-6 md:m-0">
            <div class="p-5 -mt-24 mx-auto max-w-sm bg-white rounded shadow-lg md:mt-0">
                <h2 class="text-2xl font-extrabold">Programme</h2>
                <p>Dernières actualités sur les prochains événements de l'association</p>
                <hr class="my-5">
                <ul class="list-disc px-5">
                    @forelse(\App\Models\Event::where('start_time', '>=',
                    date('Y-m-d'))->orderBy('start_time')->take(5)->get() as $event)
                    <li class="font-semibold mt-2">
                        <a href="{{ route('events.show', $event) }}">
                            {{ $event->name }}
                        </a>
                        <p class="text-sky-600">{{ $event->start_time->translatedFormat('d M Y') }}</p>
                    </li>
                    @empty
                    <li class="flex flex-col items-center justify-center">
                        <p class="text-gray-500 text-center">Aucun événement à venir</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>
