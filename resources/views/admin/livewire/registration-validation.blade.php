<div class="-mx-4 overflow-x-auto px-4 py-4 sm:-mx-8 sm:px-8">
    <div class="inline-block min-w-full overflow-hidden rounded-lg shadow">
        <table class="min-w-full table-fixed leading-normal">
            <thead>
                <tr>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Nom
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Email
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Date d'inscription
                    </th>
                    <th
                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Accepter l'inscription ?
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Models\User::where('level', \App\Enums\UserLevelEnum::class::GUEST)->get() as $user)
                    <tr>
                        <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                            <div class="ml-3">
                                <p class="whitespace-no-wrap text-gray-900">
                                    {{ $user->name }}
                                </p>
                            </div>
                        </td>
                        <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                            <div class="ml-3">
                                <p class="whitespace-no-wrap text-gray-900">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </td>
                        <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                            <p class="whitespace-no-wrap text-gray-900">
                                {{ $user->created_at->toFormattedDateString() }}
                            </p>
                        </td>
                        <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                            <div class="flex max-w-[5rem] justify-between">
                                <button type="button" wire:click="reject({{ $user->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-7 w-7 shrink-0 stroke-red-600 stroke-[1.7] hover:stroke-2"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                </button>
                                <button type="button" wire:click="accept({{ $user->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="cursor h-7 w-7 shrink-0 stroke-green-600 stroke-[1.7] hover:stroke-2"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-check-circle">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
