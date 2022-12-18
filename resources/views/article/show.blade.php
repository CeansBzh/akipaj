<x-member-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-white p-4 sm:p-8 sm:rounded-lg">
                <div class="max-w-screen-md mx-auto">
                    <article class="relative pt-10">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">
                            {{ $article->title }}
                        </h1>
                        @if ($article->published_at)
                        <div class="text-sm leading-6">
                            <dl>
                                <dt class="sr-only">Date</dt>
                                <dd class="absolute top-0 inset-x-0 text-slate-700 dark:text-slate-400">
                                    <time
                                        datetime="{{ $article->published_at }}">
                                        {{ ucfirst($article->published_at->translatedFormat('l d F, Y')) }}
                                    </time>
                                </dd>
                            </dl>
                        </div>
                        @endif
                        <div class="mt-12 prose prose-slate">
                            {!! $article->body_html !!}
                        </div>
                    </article>
                </div>
            </div>

        </div>
    </div>

</x-member-layout>
