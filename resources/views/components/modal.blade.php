<div id="modal" x-data="{ 
    show: @entangle($attributes->wire('model')).defer,
    hide() {
        this.show = false;
        let el = document.getElementById('modal').childNodes[1];
        setTimeout(() => { el.scrollTop = 0; }, 160);
    }
}" x-cloak x-show="show" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform" x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform" x-on:keydown.escape.window="hide()"
    x-on:click.away="hide()"
    class="p-2 fixed w-full h-100 inset-0 z-50 overflow-hidden flex justify-center items-center bg-black bg-opacity-75">
    <div @click.away="hide()" class="flex flex-col max-w-6xl max-h-full overflow-auto overscroll-none rounded-lg">
        <div class="relative shadow bg-white">
            <div class="flex flex-row-reverse justify-between items-center p-5 rounded-t border-b">
                <button @click="hide()" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                    data-modal-toggle="large-modal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                {{ $title ?? '' }}
            </div>
            {{ $slot }}
        </div>
    </div>
</div>