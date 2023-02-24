<?php

namespace App\Http\Livewire\Admin;

use App\Enums\UserLevelEnum;
use App\Models\User;
use Livewire\Component;
use App\Events\RegistrationValidated;

class RegistrationValidation extends Component
{
    public function accept(User $user)
    {
        $user->level = UserLevelEnum::MEMBER;
        $user->save();
        event(new RegistrationValidated($user));
    }

    public function reject(User $user)
    {
        $user->delete();
    }

    public function render()
    {
        return view('admin.livewire.registration-validation');
    }
}
