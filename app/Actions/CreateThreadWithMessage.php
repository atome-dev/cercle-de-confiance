<?php

namespace App\Actions;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use App\Services\ThreadCodeGenerator;
use App\Services\ThreadEncryptionService;
use Illuminate\Support\Facades\DB;

class CreateThreadWithMessage
{
    public function __construct(
        protected ThreadCodeGenerator $codes,
        protected ThreadEncryptionService $encryption,
    ) {}

    public function execute(
        string $senderName,
        string $senderEmail,
        string $message,
        string $recipientType,
        ?int $recipientUserId,
        ?User $connectedSender,
    ): array {
        return DB::transaction(function () use (
            $senderName, $senderEmail, $message, $recipientType, $recipientUserId, $connectedSender
        ) {
            $threadCode = $this->codes->generateThreadCode();
            $isAnonymous = $connectedSender === null;
            $privateKey = $isAnonymous ? $this->codes->generatePrivateKey() : null;

            $threadKey = $this->encryption->generateThreadKey();

            $thread = Thread::create([
                'code' => $threadCode,
                'recipient_type' => $recipientType,
                'recipient_user_id' => $recipientType === 'member' ? $recipientUserId : null,
                'status' => 'nouveau',
                'sender_name' => $senderName,
                'sender_email' => $senderEmail,
                'sender_user_id' => $connectedSender?->id,
                'is_anonymous' => $isAnonymous,
                'thread_key_envelope' => $this->encryption->sealForApp($threadKey),
                'anon_key_envelope' => $isAnonymous
                    ? $this->encryption->sealForAnon($threadKey, $threadCode, $privateKey)
                    : null,
                'last_message_at' => now(),
            ]);

            ThreadMessage::createEncrypted(
                thread: $thread,
                plaintext: $message,
                threadKey: $threadKey,
                authorType: 'sender',
                authorUserId: $connectedSender?->id,
            );

            return [
                'thread' => $thread,
                'fullCode' => $isAnonymous ? $this->codes->fullCode($threadCode, $privateKey) : null,
            ];
        });
    }
}
