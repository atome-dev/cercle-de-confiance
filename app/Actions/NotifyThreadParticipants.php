<?php

namespace App\Actions;

use App\Models\ThreadMessage;
use App\Notifications\NewThreadMessageForMember;
use App\Notifications\NewThreadMessageForSender;
use Illuminate\Support\Facades\Notification;

/**
 * Prévient par courriel (en file d'attente) toutes les personnes concernées
 * par un nouveau message, sauf son auteur :
 * - les membres détenant un grant sur le dossier ;
 * - l'expéditeur, s'il a laissé une adresse et si le message vient d'un
 *   membre et n'est pas interne.
 */
class NotifyThreadParticipants
{
    public function execute(ThreadMessage $message): void
    {
        $thread = $message->thread;

        $members = $thread->grantedUsers()
            ->when($message->author_user_id, fn ($query, $authorUserId) => $query->whereKeyNot($authorUserId))
            ->get();

        Notification::send($members, new NewThreadMessageForMember($thread));

        $shouldNotifySender = $message->author_type === 'member'
            && ! $message->is_internal
            && $thread->decryptedSenderEmail() !== '';

        if ($shouldNotifySender) {
            $thread->notify(new NewThreadMessageForSender);
        }
    }
}
