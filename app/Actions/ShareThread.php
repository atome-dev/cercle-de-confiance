<?php

namespace App\Actions;

use App\Models\Thread;
use App\Models\ThreadKeyGrant;
use App\Models\User;
use App\Services\ThreadEncryptionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShareThread
{
    public function __construct(protected ThreadEncryptionService $encryption) {}

    /**
     * @param  array<int>  $targetUserIds
     * @return Collection<int, ThreadKeyGrant>
     */
    public function execute(Thread $thread, User $grantor, array $targetUserIds): Collection
    {
        $grantorGrant = $thread->grants()->where('user_id', $grantor->id)->first();

        if (! $grantorGrant) {
            throw new AuthorizationException("Vous n'avez pas accès à ce dossier.");
        }

        $threadKey = $this->encryption->openAppEnvelope($grantorGrant->key_envelope);

        $existingUserIds = $thread->grants()->pluck('user_id')->all();
        $newUserIds = array_diff(array_unique($targetUserIds), $existingUserIds);

        return DB::transaction(function () use ($thread, $grantor, $threadKey, $newUserIds) {
            return collect($newUserIds)->map(fn ($userId) => ThreadKeyGrant::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'key_envelope' => $this->encryption->sealForApp($threadKey),
                'granted_by_user_id' => $grantor->id,
            ]));
        });
    }
}
