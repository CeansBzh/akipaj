<div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
    <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal table-fixed">
            <thead>
                <tr>
                    <th
                        class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Nom
                    </th>
                    <th
                        class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Email
                    </th>
                    <th
                        class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Date d'inscription
                    </th>
                    <th
                        class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Accepter l'inscription ?
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\User::whereRelation('roles', 'name', 'guest')->get() as $user)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="ml-3">
                            <p class="text-gray-900 whitespace-no-wrap">
                                {{ $user->name }}
                            </p>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="ml-3">
                            <p class="text-gray-900 whitespace-no-wrap">
                                {{ $user->email }}
                            </p>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">
                            {{ $user->created_at->toFormattedDateString() }}
                        </p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex max-w-[5rem] justify-between">
                            <button type="button" wire:click="reject({{ $user->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 shrink-0 stroke-red-600 stroke-[1.7] hover:stroke-2"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-x-circle">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </button>
                            <button type="button" wire:click="accept({{ $user->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-7 w-7 shrink-0 stroke-green-600 stroke-[1.7] cursor hover:stroke-2" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle">
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