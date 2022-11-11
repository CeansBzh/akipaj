<?php

namespace App\Listeners;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountCreated;
use App\Notifications\RegistrationSuccessful;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAccountCreatedNotification
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        // Information des admins de la création d'un compte
        $adminRole = Role::where('name', 'admin')->first();
        $admins = $adminRole->users;
        $admins->each(function (User $user) use ($event) {
            $user->notify(new AccountCreated($event->user));
        });
        // Information de l'utilisateur de la création de son compte
        $event->user->notify(new RegistrationSuccessful($event->user));
    }
}
