<?php

namespace App\Listeners;

use App\Events\RegistrationValidated;
use App\Notifications\RegistrationValidated as RegistrationValidatedNotification;

class SendRegistrationValidationNotification
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(RegistrationValidated $event)
    {
        $event->user->notify(new RegistrationValidatedNotification);
    }
}
