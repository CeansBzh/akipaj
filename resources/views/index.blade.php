<x-app-layout>
    <section class="relative bg-hero bg-cover bg-center bg-no-repeat">
        <div class="absolute inset-0 bg-white/50 sm:bg-transparent sm:bg-gradient-to-r sm:from-white/60">
        </div>
        <div
            class="relative mx-auto max-w-screen-xl px-4 py-32 sm:px-6 lg:flex lg:h-[calc(100vh-6rem)] lg:items-center lg:px-8">
            <div class="max-w-xl text-center sm:text-left">
                <h1 class="text-3xl font-extrabold">
                    <strong class="block font-extrabold text-yellow-500 sm:text-5xl">
                        Akipaj
                    </strong>
                    Bienvenue à bord !
                </h1>

                <div class="mt-8 flex flex-wrap gap-4 text-center">
                    <a href="{{ route('register') }}"
                        class="block w-full rounded bg-sky-800 px-12 py-3 font-medium text-white shadow hover:bg-sky-700 focus:outline-none focus:ring active:bg-sky-600 sm:w-auto">
                        Créer un compte
                    </a>
                    <a href="{{ route('login') }}"
                        class="block w-full rounded bg-white px-12 py-3 font-medium text-sky-900 shadow hover:text-sky-500 focus:outline-none focus:ring active:text-sky-400 sm:w-auto">
                        Je suis déjà membre
                    </a>
                </div>
            </div>
        </div>
        <div
            class="absolute -bottom-8 h-32 px-5 w-screen flex flex-nowrap z-10 space-x-5 justify-center text-2xl text-white font-light lg:left-24 sm:w-fit">
            <a href="#"
                class="h-full flex justify-center items-center text-center rounded-b drop-shadow-md bg-sky-900 p-2 hover:bg-sky-800 hover:ring xs:basis-1/2 sm:basis-1/3">
                Corse 2022
            </a>
            <a href="#"
                class="hidden h-full justify-center items-center text-center rounded-b drop-shadow-md cursor-pointer bg-sky-900 p-2 hover:bg-sky-800 hover:ring xs:flex xs:basis-1/2 sm:basis-1/3">
                Guadeloupe 2023
            </a>
            <a href="#"
                class="hidden h-full justify-center items-center text-center rounded-b drop-shadow-md cursor-pointer bg-sky-900 p-2 hover:bg-sky-800 hover:ring sm:flex sm:basis-1/3">
                Saint-pierre-et-miquelon 202255
            </a>
        </div>
    </section>

    <section class="bg-white">
        <div class="max-w-screen-xl mx-auto px-4 py-12 sm:px-6 md:py-24 lg:px-8">
            <div class="flex flex-wrap md:flex-nowrap">
                <div class="py-10 self-center md:mr-20">
                    <h2 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
                        L'association Akipaj
                    </h2>
                    <p class="mt-12 text-lg text-gray-500 indent-5 text-justify">
                        Après de nombreuses croisières entre amis en Bretagne Nord, Sud et Atlantique, où plaisir et
                        bonne humeur étaient à chaque fois au rendez-vous, nous poursuivons dans cette voie bien sûr et
                        souhaitons y ajouter un nouveau challenge : Nous nous lançons maintenant dans l'aventure de la
                        régate, toujours dans un esprit convivial, bien entendu.
                        Le but est de participer chaque année à 2 régates, ou plus si possible, suivant les
                        disponibilités de chacun.
                        Dans ce but, nous avons créé l’association AKIPAJ
                        (« équipage » en Breton) - Association loi 1901.
                    </p>
                </div>
                <div class="mx-auto">
                    <img class="ml-auto w-full max-w-xs h-auto md:max-w-sm lg:max-w-md md:w-auto"
                        src="{{ Vite::asset('resources/images/index_1.jpg') }}"
                        alt="Photo d'un bateau devant le coucher de soleil, photo prise aux Glénans en 2021.">
                    <p class="w-fit ml-auto p-1">Les Glénans - Juillet 2021</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <h2 class="text-5xl mb-5">Actualités</h2>
                <p class="text-lg">Les dernières news d'Akipaj</p>
                <div class="flex mx-auto w-fit">
                    <svg class="h-16 mt-5 text-sky-500 md:h-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-tool">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z">
                        </path>
                    </svg>
                    <p class="ml-8 text-2xl font-bolder self-center underline">Section à venir</p>
                </div>
            </div>
        </div>
    </section>

    <img class="w-screen max-h-72 object-cover" src="{{ Vite::asset('resources/images/index_2.jpg') }}" alt="">

    <section class="bg-white">
        <div class="max-w-screen-xl mx-auto py-12 sm:px-6 md:py-24 lg:px-8">
            <div class="text-center">
                <h2 class="text-5xl mb-5">Photos</h2>
                <x-photo-carousel />
                <a href="{{ route('photos.index') }}"
                    class="block w-fit mx-auto rounded bg-sky-800 mt-8 px-12 py-3 font-medium text-white shadow hover:bg-sky-700 focus:outline-none focus:ring active:bg-sky-600">
                    Voir toutes les photos
                </a>
            </div>
        </div>
    </section>
</x-app-layout>