@php
$userCount = $trip->users->count();
@endphp

<section class="max-w-2xl mx-auto">
    <h2 class="mb-5 font-semibold text-gray-800 leading-tight text-center text-2xl">
        Informations
    </h2>
    <div class="{{ $userCount > 6 ? '' : 'md:flex md:flex-row md:justify-around md:divide-x' }}">
        <div class="mb-4 max-w-sm mx-auto {{ $userCount > 6 ? '' : 'md:mx-0 md:flex-grow' }}">
            <h4 class="font-bold text-lg text-center">Dates</h4>
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
        @if($userCount > 0)
        <div class="mb-4 {{ $userCount > 6 ? '' : 'md:flex-1' }}">
            <h4 class="font-bold text-lg text-center">Matelots</h4>
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
        @endif
    </div>
    <div>
        <h4 class="font-bold text-lg">À propos</h4>
        <p class="text-gray-800 indent-2 text-justify">{{ $trip->description }}</p>
    </div>
</section>
<section class="max-w-5xl mx-auto">
    @if($trip->boats->count() > 0)
    <div class="mt-3">
        <h4 class="font-bold text-lg text-center mb-2">{{ $trip->boats->count() === 1 ? 'Le bateau' : 'Les bateaux' }}
        </h4>
        {{-- Desktop Table --}}
        <div class="justify-center p-2 m-0 hidden overflow-auto md:flex">
            <table class="w-full max-w-min text-sm text-center shrink-0 text-gray-700 rounded-lg shadow-md bg-white">
                <tr class="border-b border-gray-200">
                    <th class="bg-gray-50"></th>
                    <th scope="col" class="text-xs uppercase px-6 py-3 bg-gray-50">Type</th>
                    <th scope="col" class="text-xs text-gray-700 uppercase px-6 py-3 bg-gray-50">Année</th>
                    <th scope="col" class="text-xs text-gray-700 uppercase px-6 py-3 bg-gray-50">Constructeur</th>
                    <th scope="col" class="text-xs text-gray-700 uppercase px-6 py-3 bg-gray-50">Loueur</th>
                    <th scope="col" class="text-xs text-gray-700 uppercase px-6 py-3 bg-gray-50">Port</th>
                    <th scope="col" class="text-xs text-gray-700 uppercase px-6 py-3 bg-gray-50">Équipage</th>
                </tr>
                @foreach($trip->boats as $boat)
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-md text-gray-800 px-6 py-3 bg-gray-50 whitespace-nowrap">{{ $boat->name
                        }}</th>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $boat->type ?? '/' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $boat->year ?? '/' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $boat->builder ?? '/' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $boat->renter ?? '/' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $boat->city ?? '/' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap ">{{ $boat->crew ?? '/' }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        {{-- Mobile Table --}}
        <div class="relative overflow-x-auto shadow-md border rounded md:hidden">
            <table class="w-full text-sm text-left text-gray-700">
                <tr class="border-b border-gray-200">
                    <th class="bg-gray-50"></th>
                    @foreach($trip->boats as $boat)
                    <th scope="col" class="text-xs text-center text-gray-800 uppercase px-6 py-3">{{ $boat->name }}</th>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Type</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->type ?? '/' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Année</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->year ?? '/' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Constructeur</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->builder ?? '/' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Loueur</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->renter ?? '/' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Port</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->city ?? '/' }}</td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Équipage</th>
                    @foreach($trip->boats as $boat)
                    <td class="text-center">{{ $boat->crew ?? '/' }}</td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
    @endif
</section>
