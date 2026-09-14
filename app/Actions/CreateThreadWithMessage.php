<?php

namespace App\Actions;

use App\Models\Thread;
use App\Models\ThreadKeyGrant;
use App\Models\ThreadMessage;
use App\Models\User;
use App\Services\ThreadCodeGenerator;
use App\Services\ThreadEncryptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    ): array {
        return DB::transaction(function () use (
            $senderName, $senderEmail, $message, $recipientType, $recipientUserId
        ) {
            $threadCode = $this->codes->generateThreadCode();
            $privateKey = $this->codes->generatePrivateKey();

            $threadKey = $this->encryption->generateThreadKey();

            $thread = Thread::create([
                'code' => $threadCode,
                'recipient_type' => $recipientType,
                'recipient_user_id' => $recipientType === 'member' ? $recipientUserId : null,
                'status' => 'nouveau',
                'sender_name' => $this->encryption->sealIdentityForAnon($senderName, $threadCode, $privateKey),
                'sender_email' => $this->encryption->sealIdentityForAnon($senderEmail, $threadCode, $privateKey),
                'anon_key_envelope' => $this->encryption->sealForAnon($threadKey, $threadCode, $privateKey),
                'last_message_at' => now(),
            ]);

            if ($recipientType === 'group') {
                $targetUserIds = User::whereHas('roles', fn ($q) => $q->where('name', 'parent'))
                    ->pluck('id')->all();

                if ($targetUserIds === []) {
                    $targetUserIds = User::whereHas('roles', fn ($q) => $q->where('name', 'administrateur'))
                        ->pluck('id')->all();

                    Log::warning('Group thread created with no parent users — granted to administrateurs instead', [
                        'thread_id' => $thread->id,
                    ]);
                }
            } else {
                $targetUserIds = array_filter([$recipientUserId]);
            }

            foreach ($targetUserIds as $userId) {
                ThreadKeyGrant::create([
                    'thread_id' => $thread->id,
                    'user_id' => $userId,
                    'key_envelope' => $this->encryption->sealForApp($threadKey),
                    'granted_by_user_id' => null,
                ]);
            }

            ThreadMessage::createEncrypted(
                thread: $thread,
                plaintext: $message,
                threadKey: $threadKey,
                authorType: 'sender'
            );

            return [
                'thread' => $thread,
                'fullCode' => $this->codes->fullCode($threadCode, $privateKey),
            ];
        });
    }
}
