<div>
    <div class="my-2 flex flex-col sm:flex-row">
        <div class="mb-1 flex flex-row sm:mb-0">
            <select wire:model="paginate"
                class="block h-full w-full appearance-none rounded-l border border-gray-400 bg-white py-2 px-4 pr-8 leading-tight text-gray-700 focus:border-gray-500 focus:bg-white focus:outline-none">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
            </select>
            <select wire:model="level"
                class="block h-full w-full appearance-none rounded-r border-t border-r border-b border-gray-400 bg-white py-2 px-4 pr-8 leading-tight text-gray-700 focus:border-l focus:border-r focus:border-gray-500 focus:bg-white focus:outline-none sm:rounded-r-none sm:border-r-0">
                <option value="-1">Tous</option>
                @foreach (\App\Enums\UserLevelEnum::class::cases() as $level)
                    <option value="{{ $level->value }}">{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="relative block">
            <span class="absolute inset-y-0 left-0 flex h-full items-center pl-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current text-gray-500">
                    <path
                        d="M10 4a6 6 0 100 12 6 6 0 000-12zm-8 6a8 8 0 1114.32 4.906l5.387 5.387a1 1 0 01-1.414 1.414l-5.387-5.387A8 8 0 012 10z">
                    </path>
                </svg>
            </span>
            <input placeholder="Rechercher" wire:model="searchTerm" type="text"
                class="block w-full appearance-none rounded-r rounded-l border border-b border-gray-400 bg-white py-2 pl-8 pr-6 text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:text-gray-700 focus:placeholder-gray-600 focus:outline-none sm:rounded-l-none" />
        </div>
    </div>
    <div class="-mx-4 overflow-x-auto px-4 py-4 sm:-mx-8 sm:px-8">
        <div class="inline-block min-w-full overflow-hidden rounded-lg shadow">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Nom
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Date d'inscription
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Rôle
                        </th>
                        <th
                            class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-full w-full rounded-full"
                                            src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                                            alt="Photo de profil de {{ $user->name }}" />
                                    </div>
                                    <div class="ml-3">
                                        <p class="whitespace-no-wrap text-gray-900">
                                            {{ $user->name }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <p class="whitespace-no-wrap text-gray-900">
                                    {{ $user->created_at->toFormattedDateString() }}
                                </p>
                            </td>
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <span
                                    class="relative inline-block px-3 py-1 font-semibold leading-tight text-green-900">
                                    <span aria-hidden
                                        class="absolute inset-0 rounded-full bg-green-200 opacity-50"></span>
                                    <span
                                        class="relative">{{ $user->level->label() ?? 'Rôle inconnu' }}</span>
                                </span>
                            </td>
                            <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="hover:underline">Modifier</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex flex-col items-center border-t bg-white px-5 py-5 xs:flex-row xs:justify-between">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
