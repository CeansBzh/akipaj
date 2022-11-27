<section>
    <header class="flex flex-col justify-between space-y-4 sm:space-y-0 sm:flex-row">
        <div class="flex items-center mx-auto sm:mx-0">
            <h2 class="text-lg font-medium text-gray-900">Derniers virements</h2>
        </div>
        <div class="mx-auto mt-2 sm:mx-0">
            <x-primary-link class="mr-1" href="{{ route('payments.create') }}">
                Nouveau virement
            </x-primary-link>
            <x-secondary-link href="{{ route('payments.index') }}">
                Historique
            </x-secondary-link>
        </div>
    </header>

    <div class="mt-4">
        <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-2 overflow-x-auto">
            <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Montant
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Description
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestPayments as $payment)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="font-bold">
                                    {{ $payment->amount }} €
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p>
                                    {{ $payment->description }}
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p>
                                    {{ $payment->created_at->locale(config('app.locale'))->isoFormat('LLL') }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</section>