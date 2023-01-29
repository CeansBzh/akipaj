@php
    $articles = \App\Models\Article::where('online', true)
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();
@endphp

<section
    class="mx-auto flex max-w-md flex-col space-y-3 md:grid md:max-w-none md:grid-cols-[1.3fr_repeat(2,1fr)] md:gap-3 md:space-y-0">
    {{-- first article --}}
    @if ($articles->first())
        <div class="group/card relative overflow-hidden shadow-sm">
            <img src="{{ $articles->first()->imagePath }}"
                alt="Image de couverture de l'article {{ $articles->first()->title }}"
                class="absolute h-full w-full transform select-none overflow-hidden object-cover duration-500 ease-in-out group-hover/card:scale-105">
            <div class="absolute inset-0 bg-gradient-to-tr from-neutral-800/75">
            </div>
            <div class="relative top-0 flex h-full w-full max-w-sm flex-col justify-end p-5">
                <p class="mb-2 font-semibold text-sky-300">
                    {{ $articles->first()->published_at->translatedFormat('d M Y') }}
                </p>
                <a href="{{ route('articles.show', $articles->first()) }}">
                    <h5 class="mb-4 text-2xl font-bold tracking-tight text-white">{{ $articles->first()->title }}</h5>
                </a>
                <p class="mb-3 font-normal text-white">{{ $articles->first()->summary }}</p>
                <a href="{{ route('articles.show', $articles->first()) }}"
                    class="group/read-more inline-flex items-center text-center text-sm font-medium text-white">
                    Lire la suite
                    <svg aria-hidden="true"
                        class="ml-2 h-4 w-4 transform transition duration-300 ease-in-out group-hover/read-more:translate-x-1"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
    @else
        <div class="col-span-3 w-full p-12">
            <p class="text-center text-gray-500">Aucun article publié</p>
        </div>
    @endif

    {{-- second and third articles --}}
    @foreach ($articles->slice(1) as $article)
        <div class="group/card block overflow-hidden bg-white shadow-md">
            <div class="relative overflow-hidden pb-48">
                <a href="{{ route('articles.show', $article) }}">
                    <img class="absolute inset-0 h-full w-full transform select-none object-cover duration-500 ease-in-out group-hover/card:scale-105"
                        src="{{ $article->imagePath }}" alt="Image de couverture de l'article {{ $article->title }}" />
                </a>
            </div>
            <div class="p-5">
                <p class="mb-2 font-semibold text-sky-600">{{ $article->published_at->translatedFormat('d M Y') }}</p>
                <a href="{{ route('articles.show', $article) }}">
                    <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900">{{ $article->title }}</h5>
                </a>
                <p class="mb-3 font-normal text-gray-700">{{ $article->summary }}</p>
                <a href="{{ route('articles.show', $article) }}"
                    class="group/read-more inline-flex items-center text-center text-sm font-medium text-gray-600">
                    Lire la suite
                    <svg aria-hidden="true"
                        class="ml-2 h-4 w-4 transform transition duration-300 ease-in-out group-hover/read-more:translate-x-1"
                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
    @endforeach
</section>
