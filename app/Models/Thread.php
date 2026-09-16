<?php

namespace App\Models;

use App\Enums\SchoolClass;
use App\Enums\Section;
use App\Services\ThreadEncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'recipient_type', 'recipient_user_id', 'status',
        'section', 'school_class', 'comment_ciphertext', 'comment_iv', 'comment_tag',
        'sender_name', 'sender_email', 'sender_user_id', 'is_anonymous',
        'anon_key_envelope',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'section' => Section::class,
            'school_class' => SchoolClass::class,
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

    public function grants(): HasMany
    {
        return $this->hasMany(ThreadKeyGrant::class);
    }

    public function grantedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_key_grants')
            ->withPivot('granted_by_user_id')
            ->withTimestamps();
    }

    public function isForGroup(): bool
    {
        return $this->recipient_type === 'group';
    }

    /**
     * Cet utilisateur détient-il une clé (grant) pour ce dossier ?
     * L'accès n'est plus lié à un rôle : il dépend uniquement de
     * l'existence d'un ThreadKeyGrant pour cet utilisateur précis
     * (accordé automatiquement à la création ou via un partage).
     */
    public function isAccessibleBy(User $user): bool
    {
        return $this->grants()->where('user_id', $user->id)->exists();
    }

    public function decryptKeyFor(User $user): string
    {
        $grant = $this->grants()->where('user_id', $user->id)->firstOrFail();

        return app(ThreadEncryptionService::class)->openAppEnvelope($grant->key_envelope);
    }

    public function decryptKeyForAnonCode(string $privateKey): string
    {
        return app(ThreadEncryptionService::class)
            ->openAnonEnvelope($this->anon_key_envelope, $this->code, $privateKey);
    }

    public function decryptedSenderName(): string
    {
        return app(ThreadEncryptionService::class)->openTextFromAppEnvelope($this->sender_name);
    }

    public function decryptedSenderEmail(): string
    {
        return app(ThreadEncryptionService::class)->openTextFromAppEnvelope($this->sender_email);
    }

    /**
     * Chiffré comme les messages du dossier (AES-256-GCM, clé du dossier) —
     * lisible par tout titulaire d'un grant, jamais par l'application seule.
     */
    public function encryptComment(string $plaintext, string $threadKey): void
    {
        if ($plaintext === '') {
            $this->update(['comment_ciphertext' => null, 'comment_iv' => null, 'comment_tag' => null]);

            return;
        }

        $encrypted = app(ThreadEncryptionService::class)->encryptMessage($plaintext, $threadKey);

        $this->update([
            'comment_ciphertext' => $encrypted['ciphertext'],
            'comment_iv' => $encrypted['iv'],
            'comment_tag' => $encrypted['tag'],
        ]);
    }

    public function decryptComment(string $threadKey): string
    {
        if (! $this->comment_ciphertext) {
            return '';
        }

        return app(ThreadEncryptionService::class)
            ->decryptMessage($this->comment_ciphertext, $this->comment_iv, $this->comment_tag, $threadKey);
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
