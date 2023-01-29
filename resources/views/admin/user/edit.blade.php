<x-admin-layout>
    <h1 class="pb-6 text-3xl text-black">Modifier l'utilisateur</h1>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @include('admin.user.partials.edit-form')
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @include('admin.user.partials.delete-form')
            </div>

        </div>
    </div>

</x-admin-layout>
