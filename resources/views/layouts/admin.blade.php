<x-app-layout>
    <div class="flex bg-gray-200">
        <x-admin.sidebar />
        <div class="flex h-screen w-full flex-col overflow-y-hidden">
            <x-admin.header />
            <div class="flex h-full w-full flex-col overflow-x-hidden border-t">
                <main class="w-full grow p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
