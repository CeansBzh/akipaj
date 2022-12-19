<x-guest-layout>
    {{-- Hero section --}}
    <section class="relative mb-10 bg-hero bg-cover bg-center bg-no-repeat">
        <div class="absolute inset-0 bg-white/50 sm:bg-transparent sm:bg-gradient-to-r sm:from-white/60">
        </div>
        <div
            class="relative mx-auto max-w-screen-xl h-96 px-4 py-32 sm:px-6 flex items-center justify-center sm:justify-start lg:px-8">
            <div class="max-w-xl text-center sm:text-left">
                <div class="text-3xl">
                    <a href="{{ url('/') }}" class="text-yellow-500 hover:underline">
                        <h1 class="block font-bold sm:text-5xl">
                            Akipaj
                        </h1>
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap gap-4 text-center">
                    <a href="{{ route('register') }}"
                        class="block w-full rounded bg-sky-600 px-12 py-3 font-medium text-white shadow hover:bg-sky-500 focus:outline-none focus:ring active:bg-sky-600 sm:w-auto">
                        Créer un compte
                    </a>
                    <a href="{{ route('login') }}"
                        class="block w-full rounded bg-white px-12 py-3 font-medium text-sky-900 shadow hover:text-sky-500 focus:outline-none focus:ring active:text-sky-400 sm:w-auto">
                        Je suis déjà membre
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-screen-lg mx-auto">
        <h2 class="text-2xl font-sans mb-5">Mentions légales</h2>
        <div class="mb-5">
            <p class="font-semibold mb-2">Informations sur l'association Akipaj :</p>
            <ul class="mb-4">
                <li>Nom de l'association : Akipaj</li>
                <li>Adresse : 2 La Noé, 35410 Nouvoitou, France</li>
                <li>N° de téléphone : 02 99 62 97 91</li>
                <li>Le site Akipaj est publié par Laurent Briantais.</li>
            </ul>
            <p class="font-semibold mb-2">Informations sur l'hébergement du site :</p>
            <ul class="mb-3">
                <li>Site hébergé par : OVH</li>
                <li>Adresse du siège social : 2 rue Kellerman, 59100 Roubaix, France</li>
            </ul>
        </div>
    </section>


</x-guest-layout>
