<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

/**
 * Prévient la personne qui nous a contactés qu'une réponse l'attend. Le
 * destinataire est le dossier lui-même (Thread::routeNotificationForMail()) :
 * l'adresse n'est déchiffrée qu'au moment de l'envoi, jamais stockée en clair
 * dans la file. Ni le contenu du message ni le code de suivi ne sont inclus.
 */
#[DeleteWhenMissingModels]
class NewThreadMessageForSender extends Notification implements ShouldQueueAfterCommit
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
            ->subject('Une réponse vous attend sur le Cercle de confiance')
            ->markdown('mail.thread-message-for-sender', [
                'url' => route('anonymous-access'),
            ]);
    }
}
