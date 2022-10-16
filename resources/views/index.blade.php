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
                        Me créer un compte
                    </a>
                    <a href="{{ route('login') }}"
                        class="block w-full rounded bg-white px-12 py-3 font-medium text-sky-900 shadow hover:text-sky-500 focus:outline-none focus:ring active:text-sky-400 sm:w-auto">
                        Je suis déjà membre
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute -bottom-8 h-32 px-5 w-screen flex flex-nowrap space-x-5 justify-center text-2xl text-white font-light lg:left-24 sm:w-fit">
            <a href="#" class="h-full flex justify-center items-center text-center rounded-b drop-shadow-md bg-sky-900 p-2 hover:bg-sky-800 hover:ring xs:basis-1/2 sm:basis-1/3">
                Corse 2022
            </a>
            <a href="#" class="hidden h-full justify-center items-center text-center rounded-b drop-shadow-md cursor-pointer bg-sky-900 p-2 hover:bg-sky-800 hover:ring xs:flex xs:basis-1/2 sm:basis-1/3">
                Guadeloupe 2023
            </a>
            <a href="#" class="hidden h-full justify-center items-center text-center rounded-b drop-shadow-md cursor-pointer bg-sky-900 p-2 hover:bg-sky-800 hover:ring sm:flex sm:basis-1/3">
                Saint-pierre-et-miquelon 202255
            </a>
        </div>
    </section>

    <section class="bg-white">
        <div class="max-w-screen-xl mx-auto px-4 py-24 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Akipaj, c'est quoi ?
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus magnam voluptatum cupiditate
                    veritatis in, accusamus quisquam.
                </p>
            </div>
        </div>
</x-app-layout>