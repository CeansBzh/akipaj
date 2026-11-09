{{-- Interrupteur pour recevoir par e-mail les nouveaux commentaires d'un profil ou d'une photo --}}

<label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-gray-900">
    <input type="checkbox" role="switch" wire:model.live="subscribeToThread" class="peer sr-only">
    <span aria-hidden="true"
        class="relative h-7 w-12 shrink-0 rounded-full bg-gray-500 transition-colors peer-checked:bg-primary-700 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-700 after:absolute after:top-1 after:left-1 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></span>
    Me prévenir par e-mail des nouveaux commentaires
</label>
