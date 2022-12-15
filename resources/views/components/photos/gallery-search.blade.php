<div x-data="{ open: false }">
    <div class="flex justify-end p-1">
        <div class="relative w-96">
            <div class="absolute flex items-center ml-2 h-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="w-5 h-5 text-gray-500">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <x-text-input type="text" placeholder="Rechercher par titre ou légende"
                wire:model="searchTermFilter" class="px-8 py-3 w-full rounded-r-none rounded-l-md text-sm text-gray-500" />
        </div>
        <x-secondary-button class="rounded-r-md rounded-l-none focus:ring-offset-0" @click="open = ! open">
            <div class="flex space-x-1 justify-between items-center text-gray-500">
                <span class="text-sm normal-case">Filtrer</span>
                <span class="text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="w-5 h-5">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                </span>
            </div>
        </x-secondary-button>
    </div>
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95" class="mb-2">
        <x-photos.gallery-search-filters />
    </div>
</div>
