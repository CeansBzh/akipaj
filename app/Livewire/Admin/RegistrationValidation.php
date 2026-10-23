<?php

namespace App\Livewire\Admin;

use App\Enums\UserLevelEnum;
use App\Events\RegistrationValidated;
use App\Models\User;
use Livewire\Component;

class RegistrationValidation extends Component
{
    public function accept(int $userId)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user = User::findOrFail($userId);

        $user->level = UserLevelEnum::MEMBER;
        $user->save();
        event(new RegistrationValidated($user));
    }

    public function reject(int $userId)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user = User::findOrFail($userId);

        $user->delete();
    }

    public function render()
    {
        return view('admin.livewire.registration-validation');
    }
}
