<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Envoyer un virement
        </h2>
    </x-slot>

    <div class="pt-2 pb-20">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <div class="mx-auto max-w-xl">
                    @include('payment.partials.payment-form')
                </div>
            </div>
        </div>
    </div>
</x-member-layout>
