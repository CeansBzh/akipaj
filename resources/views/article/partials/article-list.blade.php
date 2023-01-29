<section class="mx-auto max-w-3xl">
    <ul class="flex max-w-full flex-col space-y-6">
        @forelse ($articles as $article)
            <li class="border-b">
                <div>
                    <a href="{{ route('articles.show', $article) }}">
                        <img src="{{ $article->imagePath }}" alt="{{ $article->title }}"
                            class="h-64 w-full rounded object-cover">
                    </a>
                </div>
                <div>
                    <h2
                        class="{{ $article->online ? '' : 'text-gray-600' }} mt-4 text-center text-3xl font-bold hover:underline">
                        <a href="{{ route('articles.show', $article) }}">{{ $article->title }}
                            {{ $article->online ? '' : ' (brouillon)' }}
                        </a>
                    </h2>
                    <p class="mt-3 text-center text-sm leading-5 text-gray-700">Publié le
                        {{ \Carbon\Carbon::parse($article->published_at)->format('d/m/Y') }}
                        {{ $article->updated_at > $article->published_at->addDay()
                            ? ' - Mis à jour le ' . $article->updated_at->format('d/m/Y')
                            : '' }}
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
                <p class="text-center text-gray-500">Aucun article</p>
            </li>
        @endforelse
    </ul>
</section>
