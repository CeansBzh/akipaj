<x-app-layout>
    <h1>Ma photo : {{ $photo->title }}</h1>
    @if(Session::has('success'))
    <div class="text-green-600">
        {{ Session::get('success') }}
    </div>
    @endif
    <img src="{{ $photo->path }}" alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}">
</x-app-layout>