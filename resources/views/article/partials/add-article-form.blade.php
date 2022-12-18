<section class="max-w-2xl mx-auto">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Ajouter un article</h2>
        <p class="mt-1 text-sm text-gray-600">L'article restera en brouillon et ne sera pas visible tant qu'il n'aura
            pas été publié</p>
    </header>

    <form id="add-article-form" method="post" action="{{ route('articles.store') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="title_input" value="Titre" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full" :value="old('title')"
                placeholder="Nouveau super article" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div>
            <div class="flex flex-col space-y-2">
                <x-input-label for="editor" value="Contenu" />
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('body')" />
            </div>
            <input id="body" type="hidden" name="body">
        </div>

        <div>
            <x-input-label for="summary_input" value="Résumé" />
            <x-textarea-input id="summary_input" name="summary" class="mt-1 block w-full"
                placeholder="Dans cet article nous parlerons de..." maxlength="350" rows="4">
                {{ old('summary')}}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->get('summary')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Enregistrer l'article</x-primary-button>

            <div class="flex space-x-2 items-center">
                <x-input-label for="published_input" value="Publier l'article immédiatement" />
                <input type="checkbox" name="online" class="rounded">
            </div>
        </div>
    </form>
</section>

@push('scripts')
<script type="text/javascript">
    document.getElementById('add-article-form').addEventListener('submit', e => {
        e.preventDefault();
        document.getElementById('body').value = editor.getMarkdown();
        e.target.submit();
    });
</script>
@endpush
