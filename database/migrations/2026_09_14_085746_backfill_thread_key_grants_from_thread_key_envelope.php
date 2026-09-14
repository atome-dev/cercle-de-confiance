<?php

use App\Models\User;
use App\Services\ThreadEncryptionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parentUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'parent')
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id')
            ->all();

        $adminUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'administrateur')
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id')
            ->all();

        $encryption = app(ThreadEncryptionService::class);

        DB::table('threads')->orderBy('id')->chunkById(50, function ($threads) use ($parentUserIds, $adminUserIds, $encryption) {
            foreach ($threads as $thread) {
                try {
                    $threadKey = $encryption->openAppEnvelope($thread->thread_key_envelope);
                } catch (Throwable) {
                    Log::error('Backfill: could not decrypt thread_key_envelope', ['thread_id' => $thread->id]);

                    continue;
                }

                if ($thread->recipient_type === 'group') {
                    $targetUserIds = $parentUserIds !== [] ? $parentUserIds : $adminUserIds;
                } else {
                    $targetUserIds = array_filter([$thread->recipient_user_id]);
                }

                $rows = collect($targetUserIds)->map(fn ($userId) => [
                    'thread_id' => $thread->id,
                    'user_id' => $userId,
                    'key_envelope' => $encryption->sealForApp($threadKey),
                    'granted_by_user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

                if ($rows !== []) {
                    DB::table('thread_key_grants')->insertOrIgnore($rows);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('thread_key_grants')->truncate();
    }
};
