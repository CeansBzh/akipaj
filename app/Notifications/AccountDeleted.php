<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre compte ' . config('app.name') . ' a bien été supprimé')
            ->line('Votre compte ' . config('app.name') . ' viens d\'être supprimé.
             Si vous êtes à l\'origine de cette action vous pouvez ignorer cet email.')
            ->line('Si vous n\'êtes pas à l\'origine de cette action contactez immédiatement un administrateur.
             Nous vous conseillons également de changer votre mot de passe là où vous le réutilisez.')
            ->line('Merci de votre visite sur ' . config('app.name') . ' et bon vent !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
