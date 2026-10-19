<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Notifications\AccountCreated;
use App\Notifications\AccountDeleted;
use App\Notifications\CommentPosted;
use App\Notifications\RegistrationSuccessful;
use App\Notifications\RegistrationValidated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_notifications_are_sent_by_mail(): void
    {
        $user = $this->createUser();
        $comment = Comment::factory()->for(Photo::factory()->for(Album::factory()), 'commentable')->create();
        $notifications = [
            new AccountCreated($user),
            new AccountDeleted(),
            new CommentPosted('Photo', $comment),
            new RegistrationSuccessful(),
            new RegistrationValidated(),
        ];

        foreach ($notifications as $notification) {
            $this->assertSame(['mail'], $notification->via($user), $notification::class);
            $this->assertSame([], $notification->toArray($user), $notification::class);
        }
    }

    public function test_account_created_mail_gives_the_new_user_details_to_admins(): void
    {
        $admin = $this->createUser();
        $newUser = $this->createUser(attributes: ['name' => 'Lucien', 'email' => 'lucien@example.com']);

        $mail = (new AccountCreated($newUser))->toMail($admin);

        $this->assertStringContainsString(config('app.name'), $mail->subject);
        $this->assertContains('Nom : Lucien, Email : lucien@example.com', $mail->introLines);
        $this->assertSame(route('admin.index'), $mail->actionUrl);
    }

    public function test_comment_posted_mail_contains_the_comment(): void
    {
        $comment = Comment::factory()->for(Photo::factory()->for(Album::factory()), 'commentable')->create(['content' => 'Bravo !']);

        $mail = (new CommentPosted('Coucher de soleil', $comment))->toMail($this->createUser());

        $this->assertContains('Bravo !', $mail->introLines);
        $this->assertStringContainsString('Coucher de soleil', implode(' ', $mail->introLines));
    }

    public function test_registration_mails(): void
    {
        $user = $this->createUser();

        $this->assertStringContainsString('Bienvenue', (new RegistrationSuccessful())->toMail($user)->subject);
        $validated = (new RegistrationValidated())->toMail($user);
        $this->assertStringContainsString('validé', $validated->subject);
        $this->assertSame(url('/'), $validated->actionUrl);
        $this->assertStringContainsString('supprimé', (new AccountDeleted())->toMail($user)->subject);
    }
}
