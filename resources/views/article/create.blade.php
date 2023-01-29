<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Écrire un nouvel article</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-white p-4 sm:rounded-lg sm:p-8">
                @include('article.partials.add-article-form')
            </div>

        </div>
    </div>

</x-member-layout>
