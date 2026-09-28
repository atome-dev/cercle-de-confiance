<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

/**
 * Confirme à la personne qui nous a contactés que son message a bien été
 * reçu, avec un lien vers la page de suivi. Comme NewThreadMessageForSender,
 * le destinataire est le dossier lui-même et le courriel ne contient ni le
 * message ni le code de suivi (qui n'est conservé nulle part).
 */
#[DeleteWhenMissingModels]
class ThreadReceivedForSender extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nous avons bien reçu votre message')
            ->markdown('mail.thread-received-for-sender', [
                'url' => route('anonymous-access'),
            ]);
    }
}
