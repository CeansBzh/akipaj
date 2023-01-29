<section>
    <header class="flex flex-col justify-between space-y-4 sm:flex-row sm:space-y-0">
        <div class="mx-auto flex items-center sm:mx-0">
            <h2 class="text-lg font-medium text-gray-900">Derniers virements</h2>
        </div>
        <div class="mx-auto mt-2 text-center sm:mx-0">
            <x-primary-link class="xs:mr-2" href="{{ route('payments.create') }}">
                Nouveau virement
            </x-primary-link>
            <x-secondary-link class="mt-2 xs:mt-0" href="{{ route('payments.index') }}">
                Historique
            </x-secondary-link>
        </div>
    </header>

    <div class="mt-4">
        <div class="-mx-4 overflow-x-auto px-4 py-2 sm:-mx-8 sm:px-8">
            <div class="inline-block min-w-full overflow-hidden rounded-lg shadow">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Montant
                            </th>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Description
                            </th>
                            <th
                                class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestPayments as $payment)
                            <tr>
                                <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                    <p class="font-bold">
                                        {{ $payment->amount }} €
                                    </p>
                                </td>
                                <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                    <p>
                                        {{ $payment->description }}
                                    </p>
                                </td>
                                <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
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
