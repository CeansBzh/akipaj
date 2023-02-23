<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Liste des membres
        </h2>
    </x-slot>

    <div class="py-12 px-5">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="gap-5 xs:grid xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($users as $user)
                    <div class="mb-5 flex items-center rounded-md border-2 border-gray-300 bg-white py-5 px-8">
                        <img class="h-16 w-16 rounded-full border-4 border-solid border-white object-cover"
                            src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                            alt="Photo de profil">
                        <a class="ml-5 text-lg font-bold text-gray-800" href="{{ route('profile.show', $user->name) }}">
                            {{ $user->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-member-layout>
