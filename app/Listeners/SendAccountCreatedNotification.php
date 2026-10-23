<?php

namespace App\Listeners;

use App\Enums\UserLevelEnum;
use App\Models\User;
use App\Notifications\AccountCreated;
use App\Notifications\RegistrationSuccessful;
use Illuminate\Auth\Events\Registered;

class SendAccountCreatedNotification
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Registered $event)
    {
        /** @var User $newUser */
        $newUser = $event->user;
        // Information des admins de la création d'un compte
        User::where('level', UserLevelEnum::ADMINISTRATOR)->each(function (User $user) use ($newUser) {
            $user->notify(new AccountCreated($newUser));
        });
        // Information de l'utilisateur de la création de son compte
        $newUser->notify(new RegistrationSuccessful);
    }
}
