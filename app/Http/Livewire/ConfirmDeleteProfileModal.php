<?php

namespace App\Http\Livewire;

use App\Enum\AlertLevelEnum;
use App\Models\User;
use App\Http\Livewire\Modal;
use App\Notifications\AccountDeleted;
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
            // Envoi d'une notification mail à l'utilisateur
            $user->notify(new AccountDeleted());
            // Suppression de l'utilisateur
            $user->delete();
            // Affichage d'un message de succès
            session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Votre compte a bien été supprimé.');
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
