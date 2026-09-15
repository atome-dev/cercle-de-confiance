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
        'section', 'school_class', 'comment',
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

    public function decryptSenderName(string $privateKey): string
    {
        return app(ThreadEncryptionService::class)
            ->openIdentityFromAnonEnvelope($this->sender_name, $this->code, $privateKey);
    }

    public function decryptSenderEmail(string $privateKey): string
    {
        return app(ThreadEncryptionService::class)
            ->openIdentityFromAnonEnvelope($this->sender_email, $this->code, $privateKey);
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
