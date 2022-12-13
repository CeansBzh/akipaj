<div x-data="{ openTab: 1 }">
    <ul class="flex flex-wrap text-center rounded-md bg-white shadow-lg sm:m-5">
        <li class="flex-1" @click="openTab = 1">
            <button class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold"
                :class="{'text-sky-800 border-sky-800 border-b-2': openTab === 1}">
                Actualités
            </button>
        </li>
        <li class="flex-1" @click="openTab = 2">
            <button class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold"
                :class="{'text-sky-800 border-sky-800 border-b-2': openTab === 2}">
                Programme
            </button>
        </li>
        <li class="flex-1" @click="openTab = 3">
            <button class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold"
                :class="{'text-sky-800 border-sky-800 border-b-2': openTab === 3}">
                Dernières sorties
            </button>
        </li>
    </ul>
    <div class="p-5" x-cloak>
        <div x-show="openTab === 1">
            @include('homepage.partials.news')
        </div>
        <div x-show="openTab === 2">
            @include('homepage.partials.events')
        </div>
        <div x-show="openTab === 3">
            @include('homepage.partials.trips')
        </div>
    </div>
</div>
