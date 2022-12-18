<x-member-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier l'article</h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-white p-4 sm:p-8 sm:rounded-lg">
                @include('article.partials.edit-article-form')
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('article.partials.delete-article-form')
            </div>

        </div>
    </div>

</x-member-layout>
