/**
 * Filtres de la galerie (resources/views/livewire/gallery.blade.php) : choix des matelots « Envoyées par »
 * et champ à saisie semi-automatique de l'album.
 */

/**
 * Texte sans accents, en minuscules, les tirets et la ponctuation comme des espaces : « ile-de-re » trouve « Île de Ré ».
 *
 * @param {string} text
 * @returns {string}
 */
export const simplifyForSearch = (text) =>
    String(text ?? '')
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .replace(/[^\p{L}\p{N}]+/gu, ' ')
        .trim();

/**
 * Vrai quand chaque mot cherché se trouve dans le texte ; une recherche vide trouve tout.
 *
 * @param {string} text
 * @param {string} query
 * @returns {boolean}
 */
export const matchesSearch = (text, query) => {
    const haystack = simplifyForSearch(text);

    return simplifyForSearch(query)
        .split(' ')
        .filter(Boolean)
        .every((word) => haystack.includes(word));
};

/**
 * Liste déroulante « Envoyées par » : des cases à cocher reliées à Livewire, et une recherche par nom.
 *
 * @param {string[]} names Noms des matelots proposés
 */
export const uploaderSelect = (names) => ({
    names,
    open: false,
    query: '',

    get matchCount() {
        return this.names.filter((name) => this.matches(name)).length;
    },

    toggle() {
        this.open ? this.close() : this.show();
    },

    show() {
        this.open = true;
        this.$nextTick(() => this.$refs.search?.focus());
    },

    close(returnFocus = false) {
        if (!this.open) return;
        this.open = false;
        this.query = '';
        if (returnFocus) this.$refs.trigger.focus();
    },

    matches(name) {
        return matchesSearch(name, this.query);
    },
});

/**
 * Champ « Album » : on tape une partie du titre, puis on choisit un seul album dans la liste proposée.
 * L'album choisi vient de Livewire ($wire.albumsFilter) : un album touché dans le bandeau s'y affiche aussi.
 *
 * @param {{id: number, title: string, details: string}[]} albums Albums qui ont des photos, du plus récent au plus ancien
 */
export const albumCombobox = (albums) => ({
    albums,
    query: '',
    open: false,
    activeIndex: 0,

    init() {
        this.query = this.selectedAlbum?.title ?? '';
        this.$watch('selectedId', () => {
            this.query = this.selectedAlbum?.title ?? '';
        });
    },

    get selectedId() {
        const filter = this.$wire.albumsFilter ?? [];

        return filter.length === 1 ? Number(filter[0]) : null;
    },

    get selectedAlbum() {
        return this.albums.find((album) => album.id === this.selectedId) ?? null;
    },

    /**
     * « Tous les albums » d'abord, puis les albums qui correspondent à la saisie.
     * Tant que le champ montre l'album choisi, tous sont proposés.
     */
    get options() {
        const showsSelection = this.selectedAlbum && this.query === this.selectedAlbum.title;
        const matching = showsSelection ? this.albums : this.albums.filter((album) => matchesSearch(album.title, this.query));

        return [{ id: null, title: 'Tous les albums', details: '' }, ...matching];
    },

    show() {
        if (this.open) return;
        this.open = true;
        this.activeIndex = Math.max(0, this.options.findIndex((option) => option.id === this.selectedId));
        this.$nextTick(() => this.scrollToActive());
    },

    close() {
        this.open = false;
        this.query = this.selectedAlbum?.title ?? '';
    },

    onInput() {
        this.open = true;
        this.activeIndex = this.options.length > 1 ? 1 : 0;
    },

    move(step) {
        if (!this.open) {
            this.show();
            return;
        }
        this.activeIndex = (this.activeIndex + step + this.options.length) % this.options.length;
        this.$nextTick(() => this.scrollToActive());
    },

    scrollToActive() {
        this.$refs.listbox?.querySelector(`[data-index="${this.activeIndex}"]`)?.scrollIntoView({ block: 'nearest' });
    },

    choose(option) {
        if (!option) return;
        this.open = false;
        this.query = option.id === null ? '' : option.title;
        if (option.id !== this.selectedId) {
            this.$wire.set('albumsFilter', option.id === null ? [] : [option.id]);
        }
    },

    optionId(index) {
        return `${this.$id('album-option')}-${index}`;
    },
});
