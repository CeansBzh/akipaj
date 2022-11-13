<x-app-layout>
	<form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
		@csrf
		<div x-data="app()" x-init="changeNextStepButton(null)" x-cloak>
			<div class="max-w-xl mx-auto px-4 py-8 flex items-center justify-center">
				@if($errors->any())
				<div id="modal" x-data="{ 
					show: true,
					hide() {
						this.show = false;
						let el = document.getElementById('modal').childNodes[1];
						setTimeout(() => { el.scrollTop = 0; }, 160);
					}
				}" x-cloak x-show="show" x-transition:enter="transition ease-out duration-200"
					x-transition:enter-start="opacity-0 transform"
					x-transition:enter-end="opacity-100 transform scale-100"
					x-transition:leave="transition ease-in duration-150"
					x-transition:leave-start="opacity-100 transform scale-100"
					x-transition:leave-end="opacity-0 transform" x-on:keydown.escape.window="hide()"
					x-on:click.away="hide()"
					class="p-2 fixed w-full h-100 inset-0 z-50 overflow-hidden flex justify-center items-center bg-black bg-opacity-75">
					<div @click.away="hide()"
						class="flex flex-col max-w-6xl max-h-full overflow-auto overscroll-none rounded-lg">
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
									<span class="sr-only">Fermer la fenêtre</span>
								</button>
							</div>
							<div class="bg-white rounded-lg p-10 flex items-center shadow justify-between text-center">
								<div>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="currentColor"
										class="mb-4 h-16 w-16 text-red-500 mx-auto">
										<g transform="matrix(.99999 0 0 .99999-58.37.882)">
											<circle cx="82.37" cy="23.12" r="24" />
											<path
												d="m87.77 23.725l5.939-5.939c.377-.372.566-.835.566-1.373 0-.54-.189-.997-.566-1.374l-2.747-2.747c-.377-.372-.835-.564-1.373-.564-.539 0-.997.186-1.374.564l-5.939 5.939-5.939-5.939c-.377-.372-.835-.564-1.374-.564-.539 0-.997.186-1.374.564l-2.748 2.747c-.377.378-.566.835-.566 1.374 0 .54.188.997.566 1.373l5.939 5.939-5.939 5.94c-.377.372-.566.835-.566 1.373 0 .54.188.997.566 1.373l2.748 2.747c.377.378.835.564 1.374.564.539 0 .997-.186 1.374-.564l5.939-5.939 5.94 5.939c.377.378.835.564 1.374.564.539 0 .997-.186 1.373-.564l2.747-2.747c.377-.372.566-.835.566-1.373 0-.54-.188-.997-.566-1.373l-5.939-5.94"
												fill="white" />
										</g>
									</svg>

									<h2 class="text-2xl mb-4 text-gray-800 font-bold">Erreurs lors de
										l'envoi</h2>

									<ul class="text-gray-600 text-sm mb-5 list-decimal">
										@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
										@endforeach
									</ul>

									<button type="button" @click="show = false"
										class="w-max block mx-auto focus:outline-none py-2 px-5 rounded-lg shadow-sm text-gray-600 bg-white hover:bg-gray-100 font-medium border">Fermer
										la fenêtre</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				@elseif (session('success'))
				<div id="modal" x-data="{ 
					show: true,
					hide() {
						this.show = false;
						let el = document.getElementById('modal').childNodes[1];
						setTimeout(() => { el.scrollTop = 0; }, 160);
					}
				}" x-cloak x-show="show" x-transition:enter="transition ease-out duration-200"
					x-transition:enter-start="opacity-0 transform"
					x-transition:enter-end="opacity-100 transform scale-100"
					x-transition:leave="transition ease-in duration-150"
					x-transition:leave-start="opacity-100 transform scale-100"
					x-transition:leave-end="opacity-0 transform" x-on:keydown.escape.window="hide()"
					x-on:click.away="hide()"
					class="p-2 fixed w-full h-100 inset-0 z-50 overflow-hidden flex justify-center items-center bg-black bg-opacity-75">
					<div @click.away="hide()"
						class="flex flex-col max-w-6xl max-h-full overflow-auto overscroll-none rounded-lg">
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
									<span class="sr-only">Fermer la fenêtre</span>
								</button>
							</div>
							<div class="bg-white rounded-lg p-10 flex items-center shadow justify-between text-center">
								<div>
									<svg class="mb-4 h-20 w-20 text-green-500 mx-auto" viewBox="0 0 20 20"
										fill="currentColor">
										<path fill-rule="evenodd"
											d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
											clip-rule="evenodd" />
									</svg>


									<h2 class="text-2xl mb-4 text-gray-800 font-bold">Succès de l'envoi</h2>

									
									<div class="text-gray-600 mb-8">
										Vous pouvez dès à présent retrouver vos photos sur la page <a href="{{ route('photos.index') }}" class="text-blue-500 hover:text-blue-700">photos</a>.
									</div>

									<button type="button" @click="show = false"
										class="w-max block mx-auto focus:outline-none py-2 px-5 rounded-lg shadow-sm text-gray-600 bg-white hover:bg-gray-100 font-medium border">Fermer
										la fenêtre</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				@endif

				<div x-show.transition="step != 'complete'" class="w-full md:min-w-[30vw]">
					<!-- Top Navigation -->
					<div class="border-b-2 py-4">
						<div class="uppercase tracking-wide text-xs font-bold text-gray-500 mb-1 leading-tight"
							x-text="`Étape: ${step} sur 2`"></div>
						<div class="flex flex-col md:flex-row md:items-center md:justify-between">
							<div class="flex-1">
								<div x-show="step === 1">
									<div class="text-lg font-bold text-gray-700 leading-tight">Importer vos photos</div>
								</div>

								<div x-show="step === 2">
									<div class="text-lg font-bold text-gray-700 leading-tight">Ranger dans un album
									</div>
								</div>
							</div>

							<div class="flex items-center md:w-64">
								<div class="w-full bg-white rounded-full mr-2">
									<div class="rounded-full bg-sky-500 text-xs leading-none h-2 text-center text-white"
										:style="'width: '+ parseInt(step / 2 * 100) +'%'"></div>
								</div>
								<div class="text-xs w-10 text-gray-600" x-text="parseInt(step / 2 * 100) +'%'"></div>
							</div>
						</div>
					</div>
					<!-- /Top Navigation -->

					<!-- Step Content -->
					<div class="py-6">
						<div x-show.transition.in="step === 1">
							<div class="mb-5 text-center">
								<x-drag-and-drop class="mb-6" />
							</div>
						</div>
						<div x-show.transition.in="step === 2">
							<div x-data="{ expanded: -1 }">
								<p id="accordion-flush-heading-1">
									<button type="button" @click="expanded = expanded === 1 ? -1 : 1"
										class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-700 border-b border-gray-300"
										aria-expanded="true" aria-controls="accordion-flush-body-1">
										<span class="flex items-center">
											<svg class="w-5 h-5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"
												xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
													clip-rule="evenodd"></path>
											</svg>
											Que sont les albums ?
										</span>
										<svg class="w-6 h-6 shrink-0" :class="{ 'rotate-180': expanded != 1 }"
											fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd"
												d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
												clip-rule="evenodd"></path>
										</svg>
									</button>
								</p>
								<div id="accordion-flush-body-1" aria-labelledby="accordion-flush-heading-1"
									x-show="expanded === 1" x-collapse>
									<div class="py-5 font-light border-b border-gray-200 text-gray-600 text-justify">
										<p class="mb-2">Le site offre la possibilité de créer des collections de photo,
											appelée <span class="font-bold">"albums"</span>. </p>
										<p class="mb-2">Un album n'est pas lié à l'utilisateur qui le crée, et tout le
											monde
											peut y ajouter ses photos. L'idée est de grouper les photos par évènement,
											par
											exemple "Corse 2022", mais tout est possible !</p>
										<p class="mb-2">Retrouvez tous les albums existants sur la page suivante: <a
												href="{{ route('albums.index') }}"
												class="text-blue-600 hover:underline">Liste des albums</a>.</p>
									</div>
								</div>

								<p id="accordion-flush-heading-2">
									<button type="button" @click="expanded = expanded === 2 ? -1 : 2"
										class="flex items-center justify-between w-full py-3 font-medium text-left text-gray-700 border-b border-gray-300"
										aria-expanded="true" aria-controls="accordion-flush-body-2">
										<span class="flex items-center">
											<svg class="w-5 h-5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"
												xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd"
													d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
													clip-rule="evenodd"></path>
											</svg>
											Dans quel album ranger mes photos ?
										</span>
										<svg class="w-6 h-6 shrink-0" :class="{ 'rotate-180': expanded != 2 }"
											fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd"
												d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
												clip-rule="evenodd"></path>
										</svg>
									</button>
								</p>
								<div id="accordion-flush-body-2" aria-labelledby="accordion-flush-heading-2"
									x-show="expanded === 2" x-collapse>
									<div class="py-5 font-light border-b border-gray-200 text-gray-600 text-justify">
										<p class="mb-2">Si vous souhaitez ranger vos photos dans un album, il y a deux
											possibilités:</p>
										<ul class="list-inside list-disc mb-2 text-left font-semibold">
											<li>Créer un nouvel album</li>
											<li>Ajouter vos photos dans un album existant</li>
										</ul>
										<p>Le formulaire ci-dessous vous permettra d'effectuer une des deux
											possibilités.
										</p>
									</div>
								</div>
							</div>

							@if(!$albums->isEmpty())
							<div class="mb-5">
								<div class="my-4 relative">
									<label for="album" class="sr-only">Liste des albums de photos</label>
									<select name="album" id="album" x-on:change.throttle="changeNextStepButton($event)"
										class="bg-gray-100 border-2 w-full p-4 pr-12 rounded-lg truncate @error('album') border-red-500 @enderror">
										<option hidden disabled selected>Liste des albums</option>
										@foreach ($albums as $year => $albumPerYear)
										<optgroup label="{{ $year }}">
											@foreach ($albumPerYear as $album)
											<option value="{{ $album->id }}">{{ $album->title }}</option>
											@endforeach
										</optgroup>
										@endforeach
									</select>
									<button type="button" id="clearBtn"
										class="absolute top-1/3 right-9 text-slate-500"><svg
											xmlns="http://www.w3.org/2000/svg" width="20" height="20"
											viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
											<line x1="18" y1="6" x2="6" y2="18"></line>
											<line x1="6" y1="6" x2="18" y2="18"></line>
										</svg></button>
									@error('album')
									<div class="text-red-500 mt-2 text-sm">
										{{ $message }}
									</div>
									@enderror
								</div>
							</div>
							<div class="strike block text-center overflow-hidden whitespace-nowrap">
								<span class="relative inline-block">Ou créer un nouveau</span>
							</div>
							@else
							<p class="mt-4">Créer un nouvel album :</p>
							@endif
							<div class="my-4">
								<label for="albumTitle" class="sr-only">Titre</label>
								<input type="text" name="albumTitle" id="albumTitle"
									x-on:input.change.throttle="changeNextStepButton($event)"
									placeholder="Titre de l'album"
									class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('albumTitle') border-red-500 @enderror"
									value="{{ old('albumTitle') }}">
								@error('albumTitle')
								<div class="text-red-500 mt-2 text-sm">
									{{ $message }}
								</div>
								@enderror
							</div>
							<div class="mb-4">
								<label for="albumDesc" class="sr-only">Description de l'album</label>
								<textarea name="albumDesc" id="albumDesc" cols="30" rows="3"
									placeholder="Description de l'album" maxlength="2048"
									class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('albumDesc') border-red-500 @enderror">{{ old('albumDesc') }}</textarea>
								@error('albumDesc')
								<div class="text-red-500 mt-2 text-sm">
									{{ $message }}
								</div>
								@enderror
							</div>
							<div class="mb-8">
								<p>Date des photos</p>
								<div>
									<span class="mr-5">
										<label class="sr-only" for="month">Mois:</label>
										<select id="month" name="albumMonth"
											class="bg-gray-100 border-2 rounded-lg @error('albumMonth') border-red-500 @enderror">
											@php
											$format = new IntlDateFormatter('fr_FR',
											IntlDateFormatter::NONE,IntlDateFormatter::NONE, NULL, NULL, "MMMM");
											$currentMonth = datefmt_format($format, now());
											@endphp
											@for ($m=1; $m<=12; $m++) @php $monthName=datefmt_format($format, mktime(0,
												0, 0, $m)) @endphp <option value="{{ $m }}" {{ $currentMonth==$monthName
												? 'selected' : '' }}>{{
												ucfirst($monthName) }}
												</option>
												@endfor
										</select>
									</span>
									<span>
										<label class="sr-only" for="year">Année:</label>
										<select id="year" name="albumYear"
											class="bg-gray-100 border-2 rounded-lg @error('albumYear') border-red-500 @enderror">
											@foreach(range(date('Y'), date('Y')-50) as $y) <option value="{{ $y }}">{{
												$y }}
											</option>
											@endforeach
										</select>
									</span>
								</div>
								@error('albumMonth')
								<div class="text-red-500 mt-2 text-sm">
									{{ $message }}
								</div>
								@enderror
								@error('albumYear')
								<div class="text-red-500 mt-2 text-sm">
									{{ $message }}
								</div>
								@enderror
							</div>

						</div>
					</div>
					<!-- / Step Content -->
				</div>
			</div>

			<!-- Bottom Navigation -->
			<div class="fixed bottom-0 left-0 right-0" x-show="step != 'complete'">
				<div class="max-w-3xl mx-auto h-20 p-5 bg-white rounded-t-xl shadow-2xl">
					<div class="flex justify-between">
						<div class="mr-2">
							<button type="button" x-show="step > 1" @click="step--, changeNextStepButton()"
								class="focus:outline-none py-2 px-2 rounded-lg shadow-sm text-center text-gray-600 bg-white hover:bg-gray-100 font-medium border sm:w-32 sm:px-5">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
									class="sm:hidden" fill="none" stroke="currentColor" stroke-width="2"
									stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
									<line x1="19" y1="12" x2="5" y2="12"></line>
									<polyline points="12 19 5 12 12 5"></polyline>
								</svg>
								<span class="hidden sm:block">Retour</span>
							</button>
						</div>
						<div id="next-step" class="grow text-right">
							<button type="button" x-show="step < 2" @click="step++, changeNextStepButton()"
								class="w-max min-w-[8rem] focus:outline-none border border-transparent py-2 px-5 rounded-lg shadow-sm text-center text-white bg-sky-500 hover:bg-sky-600 font-medium">Suivant</button>
							<button type="submit" x-show="step === 2"
								class="w-max min-w-[8rem] focus:outline-none border border-transparent py-2 px-5 rounded-lg shadow-sm text-center text-white bg-sky-500 hover:bg-sky-600 font-medium">Envoyer</button>
						</div>
					</div>
				</div>
			</div>
			<!-- / Bottom Navigation -->
		</div>
	</form>
	@push('styles')
	<style>
		.strike>span:before,
		.strike>span:after {
			content: "";
			position: absolute;
			top: 50%;
			width: 9999px;
			height: 1px;
			background: gray;
		}

		.strike>span:before {
			right: 100%;
			margin-right: 15px;
		}

		.strike>span:after {
			left: 100%;
			margin-left: 15px;
		}
	</style>
	@endpush
	@push('scripts')
	<script type="text/javascript">
		function app() {
			return {
				step: 1,
				changeNextStepButton(event) {
					const button = document.querySelectorAll('#next-step button')[1];
					const albumList = document.getElementById('album');
					const albumTitle = document.getElementById('albumTitle');

					if (event != null) {
						if (albumList.selectedIndex != 0) {
							button.innerText = 'Associer à l\'album';
						} else if (albumTitle.value != '') {
							button.innerText = 'Créer l\'album';
						} else {
							button.innerText = 'Passer l\'étape et envoyer';
						}
					} else {
						button.innerHTML = 'Passer l\'étape et envoyer';
					}
				},
			}
		}
	</script>
	@if(!$albums->isEmpty())
	<script type="text/javascript">
		let select = document.getElementById('album');
		let clearBtn = document.getElementById('clearBtn');
		clearBtn.addEventListener('click', function () {
			select.selectedIndex = 0;
			select.dispatchEvent(new Event('change'));
		});
	</script>
	@endif
	@endpush
</x-app-layout>