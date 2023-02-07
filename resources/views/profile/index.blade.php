<x-member-layout>
    <div>
        @foreach ($users as $user)
            <div>
                <a href="{{ route('profile.show', $user->name) }}">
                    {{ $user->name }}
                </a>
            </div>
        @endforeach
    </div>
</x-member-layout>
