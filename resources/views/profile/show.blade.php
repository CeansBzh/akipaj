<x-member-layout>
    <div class="mx-auto flex max-w-screen-xl flex-col space-y-5 py-12 px-5 sm:flex-row sm:space-y-0 sm:space-x-5">
        <div class="h-full grow space-y-5">
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    xmlns:svgjs="http://svgjs.com/svgjs" width="950" height="300" preserveAspectRatio="none"
                    viewBox="0 0 950 300" class="h-32 w-full object-cover md:h-52">
                    <g mask="url(&quot;#SvgjsMask1793&quot;)" fill="none">
                        <rect width="950" height="300" x="0" y="0"
                            fill="url(#SvgjsRadialGradient1794)"></rect>
                        <path d="M0 0L27.75 0L0 83.17z" fill="rgba(255, 255, 255, .1)"></path>
                        <path d="M0 83.17L27.75 0L215.47 0L0 188.12z" fill="rgba(255, 255, 255, .075)"></path>
                        <path d="M0 188.12L215.47 0L569.93 0L0 251.5z" fill="rgba(255, 255, 255, .05)"></path>
                        <path d="M0 251.5L569.93 0L776.76 0L0 255.06z" fill="rgba(255, 255, 255, .025)"></path>
                        <path d="M950 300L926.02 300L950 241.69z" fill="rgba(0, 0, 0, .1)"></path>
                        <path d="M950 241.69L926.02 300L675.38 300L950 218.86z" fill="rgba(0, 0, 0, .075)"></path>
                        <path d="M950 218.86L675.38 300L354.75 300L950 176.52z" fill="rgba(0, 0, 0, .05)"></path>
                        <path d="M950 176.51999999999998L354.75 300L142.57 300L950 55.55999999999999z"
                            fill="rgba(0, 0, 0, .025)"></path>
                    </g>
                    <defs>
                        <mask id="SvgjsMask1793">
                            <rect width="950" height="300" fill="#ffffff"></rect>
                        </mask>
                        <radialGradient cx="100%" cy="0%" r="996.24" gradientUnits="userSpaceOnUse"
                            id="SvgjsRadialGradient1794">
                            <stop stop-color="rgba(0, 90, 184, 1)" offset="0"></stop>
                            <stop stop-color="rgba(14, 165, 233, 1)" offset="1"></stop>
                        </radialGradient>
                    </defs>
                </svg>
                <div class="flex flex-col sm:flex-row">
                    <div class="flex justify-center sm:justify-start sm:pl-5 md:mb-5 md:pl-10 lg:pl-16">
                        <img class="-mt-20 h-24 w-24 rounded-full border-4 border-solid border-white object-cover md:h-32 md:w-32 lg:-mt-24 lg:h-48 lg:w-48"
                            src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                            alt="Photo de profil">
                    </div>
                    <div class="flex grow justify-between p-5">
                        <div class="font-semibold text-gray-900">
                            <h3 class="text-xl">{{ $user->name }}</h3>
                            @if ($user->city)
                                <p class="mt-2 w-min whitespace-nowrap rounded-full bg-gray-200 px-3 text-sm">
                                    {{ $user->city }}</p>
                            @endif
                        </div>
                        <div class="flex justify-center pb-3 text-gray-800">
                            <div class="flex items-center space-x-2">
                                @if ($user->photos_count > 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="h-6 text-gray-600">
                                        <path
                                            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                        </path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                    <p class="text-sm text-gray-800">{{ $user->photos_count }} photos</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-5">
                <h2 class="mb-5 text-md">Laisser un message</h2>
                <div class="py-6">
                    <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

                        <div class="mx-auto max-w-3xl bg-white p-4 shadow sm:rounded-lg sm:p-8">
                            <livewire:comments :comments="$user->comments" :commentable="$user" />
                        </div>

                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-5">
                <h2 class="mb-5 text-center text-xl">Sorties</h2>
                <ul class="mx-auto max-w-xl">
                    @forelse ($tripsByYear->keys() as $year)
                        <li class="mb-2">
                            <p class="my-2 text-center font-bold">{{ $year }}</p>
                            <ul>
                                @foreach ($tripsByYear[$year] as $trip)
                                    <li>
                                        <a href="{{ route('trips.show', $trip) }}">{{ $trip->title }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @empty
                        <li class="text-center">
                            <p class="text-gray-500">Aucune sortie</p>
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-lg bg-white p-5">
                <h2 class="mb-5 text-center text-xl">Photos</h2>
                <ul class="mx-auto max-w-xl columns-4 gap-2">
                    @forelse ($user->photos as $index => $photo)
                        @if ($index > 2 && $user->photos_count > 3)
                            <li class="relative mb-2 overflow-hidden rounded-lg">
                                <img class="aspect-square h-32 w-full object-cover" src="{{ $photo->thumb_path }}"
                                    alt="Photo">
                                <div class="absolute inset-0 flex h-full items-center bg-gray-600/75 px-2 py-1">
                                    <p class="w-full text-center text-2xl text-white">
                                        +{{ $user->photos_count - 3 }}</p>
                                </div>
                            </li>
                        @break

                    @else
                        <li class="mb-2">
                            <a href="{{ route('photos.show', $photo) }}">
                                <img class="aspect-square h-32 w-full rounded-lg object-cover"
                                    src="{{ $photo->thumb_path }}" alt="Photo">
                            </a>
                        </li>
                    @endif
                @empty
                    <li class="text-center col-span-4">
                        <p class="text-gray-500">Aucune photo</p>
                    </li>
                @endforelse
            </ul>
        </div>
        @if (isset($latestPayments))
            <hr>

            <div class="rounded-lg bg-white p-4 shadow sm:p-8">
                <div class="mx-auto max-w-3xl">
                    @include('profile.partials.latest-payments')
                </div>
            </div>
        @endif
    </div>
    {{-- Sidebar --}}
    <div class="space-y-4 sm:w-1/4">
        @if (auth()->user()->isGuest())
            <div x-data="{ animation: false }" class="px-1">
                <div role="alert"
                    class="relative mb-6 scale-90 rounded-lg bg-yellow-200 p-4 text-sm text-yellow-800 transition duration-500 ease-in-out md:scale-100"
                    :class="animation ? '-translate-y-1 scale-100 ring-offset-2 ring ring-yellow-300' : ''"
                    x-init="$nextTick(() => {
                        animation = true;
                        setTimeout(() => { animation = false; }, 500);
                    })">
                    Votre inscription n'a pas encore été validée par un administrateur. Vous ne pouvez pas
                    encore
                    accéder à toutes les pages du site.
                </div>
            </div>
        @endif

        <div class="rounded-lg bg-white p-4 shadow sm:p-8">
            <div class="mx-auto max-w-3xl">
                <section class="flex flex-col items-center justify-between space-y-4 xs:space-y-0 lg:flex-row">
                    <h2 class="text-lg font-medium text-gray-900">Gestion du compte</h2>
                    <x-primary-link class="" href="{{ route('settings.edit') }}">
                        Paramètres
                    </x-primary-link>
                </section>
            </div>
        </div>
    </div>
</div>
</x-member-layout>
