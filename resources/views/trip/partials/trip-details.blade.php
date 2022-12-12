@php
$userCount = $trip->users->count();
@endphp

<section class="max-w-2xl mx-auto">
    <h2 class="mb-5 font-semibold text-gray-800 leading-tight text-center text-2xl">
        Informations
    </h2>
    <div class="{{ $userCount > 6 ? '' : 'md:flex md:flex-row md:justify-around md:divide-x' }}">
        <div class="mb-4 max-w-sm mx-auto {{ $userCount > 6 ? '' : 'md:mx-0 md:flex-grow' }}">
            <p class="font-bold text-lg text-center">Dates</p>
            <div class="flex flex-row justify-evenly items-center">
                <div>
                    <p class="text-sm text-gray-600">Du</p>
                    <p>{{ $trip->start_date->translatedFormat('d M Y') }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-arrow-right">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
                <div>
                    <p class="text-sm text-gray-600">Au</p>
                    <p>{{ $trip->end_date->translatedFormat('d M Y') }}</p>
                </div>
            </div>
        </div>
        <div class="mb-4 {{ $userCount > 6 ? '' : 'md:flex-1' }}">
            <p class="font-bold text-lg text-center">Matelots</p>
            <div class="flex flex-wrap items-center justify-center py-3">
                @foreach($trip->users as $user)
                <div class="group relative p-1">
                    <img class="w-11 h-11 rounded-full object-cover object-right"
                        src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                        alt="Photo de profil de {{ $user->name }}">
                    <div class="absolute bottom-0 inset-x-0 flex-col items-center hidden mb-[100%] group-hover:flex">
                        <span
                            class="relative z-10 w-max p-2 text-sm leading-none text-white whitespace-no-wrap bg-neutral-800 rounded-md shadow-lg">
                            {{ $user->name }}
                        </span>
                        <div class="w-3 h-3 -mt-2 rotate-45 bg-neutral-800"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div>
        <p class="font-bold text-lg">À propos</p>
        <p class="text-gray-800 indent-2 text-justify">{{ $trip->description }}</p>
    </div>
</section>
