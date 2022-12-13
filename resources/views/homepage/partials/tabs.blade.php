<div x-data="{ openTab: 1 }">
    <ul class="flex flex-wrap justify-evenly rounded-md bg-white shadow-lg uppercase sm:m-5">
        <li @click="openTab = 1">
            <a class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold" href="#">
                Actualités
            </a>
        </li>
        <li @click="openTab = 2">
            <a class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold" href="#">
                Photos
            </a>
        </li>
        <li @click="openTab = 3">
            <a class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold" href="#">
                Dernières sorties
            </a>
        </li>
        <li @click="openTab = 3">
            <a class="bg-white inline-block py-2 px-4 text-sky-500 hover:text-sky-800 font-semibold" href="#">
                Programme
            </a>
        </li>
    </ul>
    <div class="p-5" x-cloak>
        <div x-show="openTab === 1">
            @include('homepage.partials.news')
        </div>
    </div>
</div>
