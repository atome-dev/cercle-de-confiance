<?php

namespace App\Models;

use App\Services\ThreadEncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'recipient_type', 'recipient_user_id', 'status',
        'sender_name', 'sender_email', 'sender_user_id', 'is_anonymous',
        'thread_key_envelope', 'anon_key_envelope', 'anon_key_salt',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class)->orderBy('created_at');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(ThreadRead::class);
    }

    public function isForGroup(): bool
    {
        return $this->recipient_type === 'group';
    }

    /**
     * Un membre (rôle "membre") est-il autorisé à voir ce dossier ?
     * - Dossier "groupe" : tout membre, actuel ou futur, y a accès.
     * - Dossier "member" : uniquement le membre destinataire désigné.
     */
    public function isAccessibleByMember(User $user): bool
    {
        if (! $user->hasRole('membre')) {
            return false;
        }

        return $this->isForGroup() || $this->recipient_user_id === $user->id;
    }

    public function decryptKeyForMember(): string
    {
        return app(ThreadEncryptionService::class)->openAppEnvelope($this->thread_key_envelope);
    }

    public function decryptKeyForAnonCode(string $privateKey): string
    {
        return app(ThreadEncryptionService::class)
            ->openAnonEnvelope($this->anon_key_envelope, $this->code, $privateKey);
    }

    public function lastReadAtFor(?User $user): ?Carbon
    {
        $read = $this->reads()
            ->where('reader_user_id', $user?->id)
            ->first();

        return $read?->last_read_at;
    }

    public function markReadFor(?User $user): void
    {
        $this->reads()->updateOrCreate(
            ['reader_user_id' => $user?->id],
            ['last_read_at' => now()]
        );
    }

    public function hasUnreadFor(?User $user): bool
    {
        $lastRead = $this->lastReadAtFor($user);

        $latestOtherMessage = $this->messages()
            ->when($user, function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('author_type', 'sender')
                        ->orWhere('author_user_id', '!=', $user->id);
                });
            }, function ($q) {
                $q->where('author_type', 'member');
            })
            ->latest('created_at')
            ->first();

        if (! $latestOtherMessage) {
            return false;
        }

        return $lastRead === null || $latestOtherMessage->created_at->gt($lastRead);
    }
}
