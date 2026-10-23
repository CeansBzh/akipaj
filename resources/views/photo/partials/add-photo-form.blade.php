<section class="mx-auto max-w-2xl">
    <div class="mb-5 text-center">
        <form action="{{ route('photos.store', ['album' => $album]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="dropbox" class="flex w-full items-center justify-center">
                <label for="dropbox-file"
                    class="fixed bottom-26.25 cursor-pointer rounded-lg border-2 border-sky-300 bg-white p-3 hover:bg-sky-100 md:relative md:bottom-0 md:flex md:h-56 md:w-full md:items-center md:justify-center md:border-dashed md:p-8">
                    <div id="loading-spinner" role="status"
                        class="absolute bottom-[calc(50%-1.25rem)] left-[calc(50%-1.25rem)] hidden">
                        <svg class="mr-2 inline h-10 w-10 fill-sky-500 text-gray-300 motion-safe:animate-spin"
                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor" />
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill" />
                        </svg>
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <div id="drag-over-icon" role="status"
                        class="absolute bottom-[calc(50%-1.75rem)] left-[calc(50%-1.75rem)] hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="mr-2 inline h-14 w-14 text-sky-500 motion-safe:animate-bounce">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span class="sr-only">Relâcher pour déposer les fichiers</span>
                    </div>
                    <div id="dropbox-body" class="flex flex-row items-center justify-center text-sky-600 md:flex-col">
                        <svg aria-hidden="true" class="mb-3 mr-3 h-10 w-10 text-sky-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <div class="flex flex-col">
                            <p class="mb-2 hidden text-sm text-gray-500 md:block"><span class="font-semibold">Cliquez
                                    pour choisir
                                    des
                                    images</span><span id="draggableMsg" class="hidden"> ou faites-les glisser</span>
                            </p>
                            <p class="text-sm font-semibold md:hidden">Parcourir mes fichiers</p>
                            <p class="text-xs md:text-gray-500">PNG, JPG ou GIF</p>
                        </div>
                    </div>
                    <input id="dropbox-file" type="file" name="files[]" class="hidden" multiple accept="image/png, image/jpeg, image/gif" />
                </label>
            </div>

            <p id="fileNb" class="text-center"></p>
            <ul id="uploadErrors" class="text-center text-red-500"></ul>
            <div id="previewContainer" class="mt-4 pb-3">
                <div id="info" class="px-5 sm:py-10">
                    <div class="mx-auto max-w-xs text-sm text-gray-500">
                        @if ($errors->any())
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="mx-auto mb-5 h-14 w-14 text-red-500">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <div class="text-red-500">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li class="mb-2">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="mx-auto mb-5 h-14 w-14 text-gray-400">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <p>Toutes les photos importées seront redimensionnées pour réduire leur
                                poids.</p>
                        @endif
                    </div>
                </div>
            </div>

            <template id="previewElement">
                <div class="mb-5 rounded-md">
                    <div class="flex items-center justify-between">
                        <canvas class="rounded-sm" width="25" height="25">
                            Désolé, votre navigateur ne prend pas en charge &lt;canvas&gt;.
                        </canvas>
                        <div class="flex w-full flex-row justify-end rounded-r bg-white py-2 px-4 shadow-xs">
                            <span class="truncate px-3 text-base font-medium text-[#07074D]">
                                fichier.png
                            </span>
                            <button class="ml-4 text-[#07074D]">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.279337 0.279338C0.651787 -0.0931121 1.25565 -0.0931121 1.6281 0.279338L9.72066 8.3719C10.0931 8.74435 10.0931 9.34821 9.72066 9.72066C9.34821 10.0931 8.74435 10.0931 8.3719 9.72066L0.279337 1.6281C-0.0931125 1.25565 -0.0931125 0.651788 0.279337 0.279338Z"
                                        fill="currentColor" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.279337 9.72066C-0.0931125 9.34821 -0.0931125 8.74435 0.279337 8.3719L8.3719 0.279338C8.74435 -0.0931127 9.34821 -0.0931123 9.72066 0.279338C10.0931 0.651787 10.0931 1.25565 9.72066 1.6281L1.6281 9.72066C1.25565 10.0931 0.651787 10.0931 0.279337 9.72066Z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="fixed bottom-0 left-0 right-0">
                <div class="mx-auto h-20 max-w-3xl rounded-t-xl bg-white p-5 shadow-2xl">
                    <div class="flex items-center justify-between">
                        <p class="mr-2 grow overflow-hidden text-ellipsis whitespace-nowrap text-left">Vos photos
                            seront ajoutées à l'album "
                            <span class="font-semibold">{{ $album->title }}</span>"
                        </p>
                        <div>
                            <x-primary-button
                                class="w-max bg-sky-500 py-3 hover:bg-sky-700 disabled:cursor-wait disabled:opacity-50">
                                Ajouter mes photos
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

@push('scripts')
    @vite('resources/js/add-photo-form.js')
@endpush
