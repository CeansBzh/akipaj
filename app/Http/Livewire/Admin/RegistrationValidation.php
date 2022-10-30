<?php

namespace App\Http\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class RegistrationValidation extends Component
{
    public function accept(User $user) {
        $user->roles()->sync([Role::where('name', 'member')->first()->id]);
    }

    public function reject(User $user) {
        $user->delete();
    }

    public function render()
    {
        return view('livewire.admin.registration-validation');
    }
}
