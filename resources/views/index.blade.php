<x-guest-layout>
    {{-- Hero section --}}
    <section class="relative mb-10">
        <div class="absolute inset-0">
            <picture>
                {{-- XL (1536px to infinite) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.avif') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.webp') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-xl.jpg') }}" media="(min-width: 1536px)"
                    width="2600" height="1733" type="image/jpeg">
                {{-- Large (1024px to 1535px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.avif') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.webp') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.jpg') }}"
                    media="(min-width: 1024px) and (max-width: 1535px)" width="1535" height="1023" type="image/jpeg">
                {{-- Medium (768px to 1023px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.avif') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.webp') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/webp">
                <source srcset="{{ Vite::asset('resources/images/hero/hero-md.jpg') }}"
                    media="(min-width: 768px) and (max-width: 1023px)" width="1023" height="682" type="image/jpeg">
                {{-- Small (to 767px) --}}
                <source srcset="{{ Vite::asset('resources/images/hero/hero.avif') }}" media="(max-width: 767px)"
                    width="767" height="511" type="image/avif">
                <source srcset="{{ Vite::asset('resources/images/hero/hero.webp') }}" media="(max-width: 767px)"
                    width="767" height="511" type="image/webp">
                {{-- Fallback image = small JPG --}}
                <img class="w-full h-full object-cover" src="{{ Vite::asset('resources/images/hero/hero.jpg') }}"
                    alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
                    width="767" height="511" loading="lazy" decoding="async">
            </picture>
        </div>
        <div
            class="relative mx-auto max-w-screen-xl min-h-[27rem] h-[calc(100vh-6rem)] px-4 py-32 sm:px-6 flex items-center justify-center sm:justify-start lg:px-8">
            <div class="max-w-xl text-center sm:text-left">
                <div class="text-3xl">
                    <x-application-logo class="h-20" />
                    <p class="shadow-gray-100 text-shadow">Bienvenue à bord !</p>
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
        <div
            class="absolute -bottom-8 h-16 px-5 w-screen flex flex-nowrap z-10 space-x-3 justify-evenly text-lg text-white font-light md:px-40 xl:px-80">
            <a href="#croisière"
                class="w-1/3 h-full flex justify-center items-center text-center rounded-sm drop-shadow-md bg-sky-600 p-2 hover:bg-sky-500 hover:ring basis-1/3">
                Croisière
            </a>
            <a href="#régate"
                class="w-1/3 h-full flex justify-center items-center text-center rounded-sm drop-shadow-md bg-sky-600 p-2 hover:bg-sky-500 hover:ring basis-1/3">
                Régate
            </a>
            <a href="#photos"
                class="w-1/3 h-full flex justify-center items-center text-center rounded-sm drop-shadow-md bg-sky-600 p-2 hover:bg-sky-500 hover:ring basis-1/3">
                Photos
            </a>
        </div>
    </section>

    {{-- About section --}}
    <section class="max-w-screen-xl mx-auto">
        <div class="max-w-screen-xl mx-auto px-4 py-5 sm:px-6 md:py-24 lg:px-8">
            <div class="flex flex-wrap md:flex-nowrap">
                <div class="self-center mb-3 md:mr-20">
                    <h2 class="text-4xl font-bold text-gray-900 sm:text-4xl">
                        L'association Akipaj
                    </h2>
                    <p class="my-8 text-gray-600 indent-5 text-justify">
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
                    <picture>
                        <source srcset="{{ Vite::asset('resources/images/index_1.webp') }}" type="image/webp">
                        <img class="ml-auto w-full max-w-xs h-auto md:max-w-sm lg:max-w-md md:w-auto"
                            src="{{ Vite::asset('resources/images/index_1.jpg') }}"
                            alt="Photo d'un bateau devant le coucher de soleil." width="448" height="672" loading="lazy"
                            decoding="async">
                    </picture>
                    <p class="w-fit ml-auto p-1 text-sm text-gray-500">Les Glénans - Juillet 2021</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Trips section --}}
    <section id="croisière" class="max-w-screen-2xl mx-auto">
        <div class="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:h-screen lg:grid-cols-2">
                <div class="relative z-10 lg:py-16">
                    <div class="relative h-56 sm:h-80 lg:h-full">
                        <picture>
                            <source srcset="{{ Vite::asset('resources/images/index_2.webp') }}" type="image/webp">
                            <img class="absolute inset-0 h-full w-full object-cover"
                                src="{{ Vite::asset('resources/images/index_2.jpg') }}"
                                alt="Photo prise depuis le pont d'un bateau vers la proue, avec un couche de soleil sur la côte."
                                width="631" height="420" loading="lazy" decoding="async">
                        </picture>
                    </div>
                </div>

                <div class="relative flex items-center bg-gray-100">
                    <span class="hidden lg:absolute lg:inset-y-0 lg:-left-16 lg:block lg:w-16 lg:bg-gray-100"></span>

                    <div class="p-8 sm:p-16 lg:p-24">
                        <h2 class="text-3xl font-bold sm:text-4xl">Des croisières...</h2>

                        <p class="mt-4 text-gray-600">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid,
                            molestiae! Quidem est esse numquam odio deleniti, beatae, magni
                            dolores provident quaerat totam eos, aperiam architecto eius quis
                            quibusdam fugiat dicta.
                        </p>

                        <a href="{{ route('trips.index') }}"
                            class="mt-8 inline-block rounded border border-sky-600 bg-sky-600 px-12 py-3 text-sm font-medium text-white hover:bg-transparent hover:text-sky-600 focus:outline-none focus:ring active:text-sky-500">
                            Voir les croisières
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Regatta section --}}
    <section id="régate">
        <div class="max-w-screen-lg mx-auto px-4 py-5 sm:px-6 md:py-24 lg:px-8">
            <div class="flex flex-wrap md:flex-nowrap">
                <div class="self-center mb-3 md:mr-20">
                    <h2 class="text-3xl text-right font-bold text-gray-900 sm:text-4xl">
                        .. et des régates
                    </h2>
                    <p class="mt-8 text-gray-600 indent-5 text-justify">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reiciendis nam qui officia dolores
                        maiores ullam sit vitae suscipit quos, cum esse quam, dolor magnam tenetur nesciunt debitis
                        omnis facilis et! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reiciendis nam qui
                        officia dolores
                        maiores ullam sit vitae suscipit quos, cum esse quam, dolor magnam tenetur nesciunt debitis
                        omnis facilis et!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <picture>
        <source srcset="{{ Vite::asset('resources/images/index_3.webp') }}" type="image/webp">
        <img class="w-screen max-h-72 object-cover" src="{{ Vite::asset('resources/images/index_3.jpg') }}"
            alt="Photo artistique d'un noeud de cordage de bateau avec un ferry flou en arrière-plan." width="2600"
            height="834" loading="lazy" decoding="async">
    </picture>

    <section id="photos">
        <div class="max-w-screen-xl mx-auto py-12 sm:px-6 md:py-24 lg:px-8">
            <div class="text-center">
                <h2 class="text-5xl mb-5">Photos</h2>
                <p class="text-lg mb-5">Partagez vos photos avec les autres membres de l'association !</p>
                <x-photo-carousel />
                <a href="{{ route('photos.index') }}"
                    class="mt-8 inline-block rounded border border-sky-600 bg-sky-600 px-12 py-3 text-sm font-medium text-white hover:bg-transparent hover:text-sky-600 focus:outline-none focus:ring active:text-sky-500">
                    Voir les photos
                </a>
            </div>
        </div>
    </section>


</x-guest-layout>
