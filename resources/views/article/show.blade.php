<x-member-layout>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="relative bg-white p-4 sm:rounded-lg sm:p-8">
                <div class="mx-auto max-w-screen-md">
                    <article class="relative pt-10">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">
                            {{ $article->title }}
                        </h1>
                        @if ($article->published_at)
                            <div class="text-sm leading-6">
                                <dl>
                                    <dt class="sr-only">Date</dt>
                                    <dd class="absolute inset-x-0 top-0 text-slate-700 dark:text-slate-400">
                                        <time datetime="{{ $article->published_at }}">
                                            {{ ucfirst($article->published_at->translatedFormat('l d F, Y')) }}
                                        </time>
                                    </dd>
                                </dl>
                            </div>
                        @endif
                        <div class="prose prose-slate mt-12">
                            {!! $article->body_html !!}
                        </div>
                    </article>
                </div>

                <div class="absolute top-4 right-3">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-6 text-gray-600">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('update', $article)
                                <x-dropdown-link :href="route('articles.edit', $article)">
                                    Modifier l'article
                                </x-dropdown-link>
                            @endcan
                            <x-dropdown-link href="#">
                                Partager
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

        </div>
    </div>

</x-member-layout>
