<x-admin-layout>
    <h1 class="pb-6 text-3xl text-black">Panneau d'administration</h1>
    <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <div class="flex h-16 items-center justify-center rounded bg-white py-3">
            <p>Nombre d'utilisateurs : {{ \App\Models\User::count() }}</p>
        </div>
        <div class="flex h-16 items-center justify-center rounded bg-white py-3">
            <p>Nombre de photos : {{ \App\Models\Photo::count() }}</p>
        </div>
        <div class="flex h-16 items-center justify-center rounded bg-white py-3">
            <p>Nombre de commentaires : {{ \App\Models\Comment::count() }}</p>
        </div>
    </div>
    <div class="border-t">
        <livewire:admin.registration-validation>
    </div>
</x-admin-layout>
