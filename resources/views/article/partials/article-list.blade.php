<section class="mx-auto max-w-3xl">
    <ul class="flex flex-col space-y-6 max-w-full">
        @forelse ($articles as $article)
        <li class="border-b">
            <div>
                <a href="{{ route('articles.show', $article) }}">
                    <img src="{{ $article->imagePath }}" alt="{{ $article->title }}"
                        class="w-full h-64 object-cover rounded">
                </a>
            </div>
            <div>
                <h2
                    class="text-3xl font-bold text-center mt-4 hover:underline {{ $article->online ? '' : 'text-gray-600' }}">
                    <a href="{{ route('articles.show', $article) }}">{{ $article->title }} {{ $article->online ?
                        '' : ' (brouillon)' }}
                    </a>
                </h2>
                <p class="text-sm text-center leading-5 text-gray-700 mt-3">Publié le
                    {{ \Carbon\Carbon::parse($article->published_at)->format('d/m/Y') }} {{ $article->updated_at >
                    $article->published_at->addDay() ? ' - Mis à jour le '.$article->updated_at->format('d/m/Y') : '' }}
                </p>
                <div class="p-4">
                    <p class="mb-1">{{ $article->summary }}</p>
                    <div class="text-right">
                        <x-primary-link href="{{ route('articles.show', $article) }}" class="ml-2">
                            Lire la suite
                        </x-primary-link>
                    </div>
                </div>
            </div>
        </li>
        @empty
        <li class="flex flex-col items-center justify-center">
            <p class="text-gray-500 text-center">Aucun article</p>
        </li>
        @endforelse
    </ul>
</section>
