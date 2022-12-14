<x-admin-layout>
    <h1 class="text-3xl text-black pb-6">Modifier l'utilisateur</h1>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('admin.user.partials.edit-form')
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('admin.user.partials.delete-form')
            </div>

        </div>
    </div>

</x-admin-layout>
