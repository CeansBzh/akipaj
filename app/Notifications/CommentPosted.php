<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CommentPosted extends Notification
{
    use Queueable;

    protected $commentableTitle = '';
    protected $comment = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $commentableTitle, Comment $comment)
    {
        $this->commentableTitle = $commentableTitle;
        $this->comment = $comment;
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
            ->subject('Nouveau commentaire dans un fil auquel vous êtes abonné')
            ->line('Un nouveau commentaire viens d\'être posté sous ' . $this->commentableTitle . '.')
            ->line('Contenu du commentaire :')
            ->line($this->comment->content);
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
