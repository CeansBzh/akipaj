<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Articles</h2>
            @can('create', App\Models\Article::class)
            <x-primary-link href="{{ route('articles.create') }}">
                Nouvel article
            </x-primary-link>
            @endcan
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8 sm:rounded-lg">
                @include('article.partials.article-list')
            </div>

        </div>
    </div>

</x-member-layout>
