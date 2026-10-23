<x-member-layout>
    <div>
        @include('homepage.partials.hero')
    </div>

    <div class="mx-auto max-w-(--breakpoint-xl)">
        <div>
            @include('homepage.partials.tabs')
        </div>
    </div>

    <div class="mx-auto max-w-(--breakpoint-xl)">
        <div>
            @include('homepage.partials.useful-links')
        </div>
    </div>
</x-member-layout>
