<?php

namespace App\Actions;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;

class ReplyToThread
{
    public function execute(
        Thread $thread,
        string $threadKey,
        string $message,
        string $authorType,
        ?User $authorUser = null,
    ): ThreadMessage {
        $created = ThreadMessage::createEncrypted(
            $thread,
            $message,
            $threadKey,
            $authorType,
            $authorUser?->id,
        );

        if ($thread->status === 'nouveau') {
            $thread->update(['status' => 'en_cours']);
        }

        $thread->markReadFor($authorUser);

        return $created;
    }
}
