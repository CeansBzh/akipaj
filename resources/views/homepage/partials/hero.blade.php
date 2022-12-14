<section class="md:bg-hero bg-cover bg-center bg-no-repeat">
    <div class="max-w-screen-2xl mx-auto md:flex">
        {{-- Left/Top side --}}
        <div class="bg-hero bg-cover bg-center bg-no-repeat md:bg-none md:flex-grow">
            <div class="relative mx-auto max-w-screen-xl min-h-[20rem] h-full">
                <div class="text-center pt-12 md:text-left md:ml-12 md:pt-40 lg:ml-24">
                    <h1 class="text-7xl text-white mb-2">Akipaj</h1>
                    <p class="text-xl text-amber-300 mb-3">Bienvenue à bord !</p>
                    <a href="{{ route('profile.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-100 text-gray-100 rounded
                font-semibold text-sm tracking-widest shadow-sm hover:border-white hover:text-white hover:bg-gray-100/10 focus:outline-none
                focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Mon profil
                    </a>
                </div>
            </div>
        </div>

        {{-- Events section --}}
        <div class="w-full px-2 mb-5 md:w-fit md:p-6 md:m-0">
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
