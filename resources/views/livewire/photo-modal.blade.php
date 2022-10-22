<div>
    @if(isset($photo))
    <x-modal wire:model="show">
        <x-slot name="title">
            <h3 class="text-xl font-medium text-gray-900 mr-2">{{ $photo->title }}</h3>
        </x-slot>

        <div class="p-3 space-y-2">
            <img alt="{{ $photo->legend }}" class="mx-auto object-contain max-h-[80vh]" src="{{ $photo->path }}">
            <p class="text-sm font-light">{{ $photo->legend }}</p>
        </div>
        <div class="flex items-center px-5 py-2 space-x-2 rounded-b border-t border-gray-200">
            <livewire:comments :comments="$photo->comments" :commentable="$photo" />
        </div>
    </x-modal>
    @endif
</div>