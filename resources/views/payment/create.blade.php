<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Envoyer un virement
        </h2>
    </x-slot>

    <div class="pt-2 pb-20">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    @include('payment.partials.payment-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>