<?php

namespace App\Http\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Events\RegistrationValidated;

class RegistrationValidation extends Component
{
    public function accept(User $user) {
        $user->roles()->sync([Role::where('name', 'member')->first()->id]);
        event(new RegistrationValidated($user));
    }

    public function reject(User $user) {
        $user->delete();
    }

    public function render()
    {
        return view('admin.livewire.registration-validation');
    }
}
