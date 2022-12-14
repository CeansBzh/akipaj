<x-app-layout>
    <div class="bg-gray-200 flex">
        <x-admin.sidebar />
        <div class="w-full flex flex-col h-screen overflow-y-hidden">
            <x-admin.header />
            <div class="w-full h-full overflow-x-hidden border-t flex flex-col">
                <main class="w-full flex-grow p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
