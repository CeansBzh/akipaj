<x-app-layout>
    @if($trip->imagePath)
    <div class="absolute w-screen">
        <div class="absolute inset-0 from-transparent via-gray-100/10 to-gray-100 sm:bg-gradient-to-b">
        </div>
        <img src="{{ $trip->imagePath }}" alt="Image de couverture de l'événement {{ $trip->name }}"
            class="w-full object-cover min-h-[150px]">
    </div>
    @endif

    <div class="relative w-full py-12">
        <div class="max-w-7xl mx-auto flex flex-col space-y-4 sm:px-6 lg:px-8">
            <div class="mb-5 p-3 rounded-lg bg-gray-100/80 w-fit mx-auto sm:p-5 sm:mt-12 sm:mb-16">
                <h1 class="text-2xl font-bold text-gray-900 text-center sm:text-5xl lg:text-6xl">
                    {{ $trip->title }}
                </h1>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('trip.partials.trip-details')
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('trip.partials.trip-related-albums')
            </div>

        </div>
    </div>

    @push('scripts')
    <script>function showTooltip(flag) {
            switch (flag) {
                case 1:
                    document.getElementById("tooltip1").classList.remove("hidden");
                    break;
                case 2:
                    document.getElementById("tooltip2").classList.remove("hidden");
                    break;
                case 3:
                    document.getElementById("tooltip3").classList.remove("hidden");
                    break;
            }
        }
        function hideTooltip(flag) {
            switch (flag) {
                case 1:
                    document.getElementById("tooltip1").classList.add("hidden");
                    break;
                case 2:
                    document.getElementById("tooltip2").classList.add("hidden");
                    break;
                case 3:
                    document.getElementById("tooltip3").classList.add("hidden");
                    break;
            }
        }
    </script>
    @endpush

</x-app-layout>