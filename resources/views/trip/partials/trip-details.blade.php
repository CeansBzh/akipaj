@php
    $userCount = $trip->users->count();
@endphp

<section class="mx-auto max-w-2xl">
    <h2 class="mb-5 text-center text-2xl font-semibold leading-tight text-gray-800">
        Informations
    </h2>
    <div class="{{ $userCount > 6 ? '' : 'md:flex md:flex-row md:justify-around md:divide-x' }}">
        <div class="{{ $userCount > 6 ? '' : 'md:mx-0 md:flex-grow' }} mx-auto mb-4 max-w-sm">
            <h4 class="text-center text-lg font-bold">Dates</h4>
            <div class="flex flex-row items-center justify-evenly">
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
        @if ($userCount > 0)
            <div class="{{ $userCount > 6 ? '' : 'md:flex-1' }} mb-4">
                <h4 class="text-center text-lg font-bold">Matelots</h4>
                <div class="flex flex-wrap items-center justify-center py-3">
                    @foreach ($trip->users as $user)
                        <div class="group relative p-1">
                            <img class="h-11 w-11 rounded-full object-cover object-right"
                                src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                                alt="Photo de profil de {{ $user->name }}">
                            <div
                                class="absolute inset-x-0 bottom-0 mb-[100%] hidden flex-col items-center group-hover:flex">
                                <span
                                    class="whitespace-no-wrap relative z-10 w-max rounded-md bg-neutral-800 p-2 text-sm leading-none text-white shadow-lg">
                                    {{ $user->name }}
                                </span>
                                <div class="-mt-2 h-3 w-3 rotate-45 bg-neutral-800"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <div>
        <h4 class="text-lg font-bold">À propos</h4>
        <p class="text-justify indent-2 text-gray-800">{{ $trip->description }}</p>
    </div>
</section>
<section class="mx-auto max-w-5xl">
    @if ($trip->boats->count() > 0)
        <div class="mt-3">
            <h4 class="mb-2 text-center text-lg font-bold">
                {{ $trip->boats->count() === 1 ? 'Le bateau' : 'Les bateaux' }}
            </h4>
            {{-- Desktop Table --}}
            <div class="m-0 hidden justify-center overflow-auto p-2 md:flex">
                <table
                    class="w-full max-w-min shrink-0 rounded-lg bg-white text-center text-sm text-gray-700 shadow-md">
                    <tr class="border-b border-gray-200">
                        <th class="bg-gray-50"></th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase">Type</th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-700">Année</th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-700">Constructeur
                        </th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-700">Loueur</th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-700">Port</th>
                        <th scope="col" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-700">Équipage</th>
                    </tr>
                    @foreach ($trip->boats as $boat)
                        <tr class="border-b border-gray-200">
                            <th scope="row"
                                class="text-md whitespace-nowrap px-6 py-3 font-semibold uppercase text-gray-700">
                                {{ $boat->name }}</th>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->type ?? '/' }}</td>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->year ?? '/' }}</td>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->builder ?? '/' }}</td>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->renter ?? '/' }}</td>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->city ?? '/' }}</td>
                            <td class="whitespace-nowrap px-4 py-2">{{ $boat->crew ?? '/' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            {{-- Mobile Table --}}
            <div class="relative overflow-x-auto rounded border shadow-md md:hidden">
                <table class="w-full text-left text-sm text-gray-700">
                    <tr class="border-b border-gray-200">
                        <th class="bg-gray-50"></th>
                        @foreach ($trip->boats as $boat)
                            <th scope="col" class="px-6 py-3 text-center text-xs uppercase text-gray-800">
                                {{ $boat->name }}</th>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Type</th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->type ?? '/' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Année</th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->year ?? '/' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Constructeur
                        </th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->builder ?? '/' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Loueur</th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->renter ?? '/' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Port</th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->city ?? '/' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200">
                        <th scope="row" class="bg-gray-50 px-6 py-3 text-xs uppercase text-gray-800">Équipage</th>
                        @foreach ($trip->boats as $boat)
                            <td class="text-center">{{ $boat->crew ?? '/' }}</td>
                        @endforeach
                    </tr>
                </table>
            </div>
        </div>
    @endif
</section>
