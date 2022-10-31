<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Http\Livewire\Modal;
use Illuminate\Support\Facades\Hash;

class ConfirmDeleteProfileModal extends Modal
{
    public $password;

    public function delete()
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);
        $user = User::find(auth()->user()->id);
        if (Hash::check($this->password, $user->password)) {
            $user->delete();
            // TODO Suppression des données après 30 jours
            // TODO Message de confirmation de la suppression sur l'accueil et envoi notif
            return redirect('/');
        } else {
            $this->addError('password', 'Le mot de passe est incorrect.');
        }
    }

    public function render()
    {
        return view('livewire.confirm-delete-profile-modal');
    }
}
