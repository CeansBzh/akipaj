<x-admin-layout>
    <h1 class="text-3xl text-black pb-6">Panneau d'administration</h1>
    <div class="w-full grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <div class="h-16 bg-white py-3 rounded flex justify-center items-center">
            <p>Nombre d'utilisateurs : {{ \App\Models\User::count() }}</p>
        </div>
        <div class="h-16 bg-white py-3 rounded flex justify-center items-center">
            <p>Nombre de photos : {{ \App\Models\Photo::count() }}</p>
        </div>
        <div class="h-16 bg-white py-3 rounded flex justify-center items-center">
            <p>Nombre de commentaires : {{ \App\Models\Comment::count() }}</p>
        </div>
    </div>
    <div class="border-t">
caca
    </div>
</x-admin-layout>