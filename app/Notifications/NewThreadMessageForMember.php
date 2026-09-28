<?php

namespace App\Notifications;

use App\Models\Thread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

/**
 * Prévient un membre granté qu'un nouveau message est arrivé sur un dossier.
 * Le contenu du message n'est jamais inclus (il est chiffré avec la clé du
 * dossier et ne doit pas transiter en clair par la file ni par le courriel).
 */
#[DeleteWhenMissingModels]
class NewThreadMessageForMember extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(public Thread $thread) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Évalué au moment de l'envoi (dans le worker) : un membre qui désactive
     * les notifications dans son profil ne reçoit plus les courriels déjà en
     * file d'attente.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable->receives_email_notifications;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau message sur le dossier '.$this->thread->code)
            ->markdown('mail.thread-message-for-member', [
                'thread' => $this->thread,
                'url' => route('threads.show', $this->thread),
            ]);
    }
}
