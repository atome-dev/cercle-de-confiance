<?php

namespace App\Models;

use App\Services\ThreadEncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id', 'author_type', 'author_user_id',
        'ciphertext', 'iv', 'tag',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function decrypt(string $threadKey): string
    {
        return app(ThreadEncryptionService::class)
            ->decryptMessage($this->ciphertext, $this->iv, $this->tag, $threadKey);
    }

    public static function createEncrypted(
        Thread $thread,
        string $plaintext,
        string $threadKey,
        string $authorType,
        ?int $authorUserId = null,
    ): self {
        $encrypted = app(ThreadEncryptionService::class)->encryptMessage($plaintext, $threadKey);

        $message = static::create([
            'thread_id' => $thread->id,
            'author_type' => $authorType,
            'author_user_id' => $authorUserId,
            'ciphertext' => $encrypted['ciphertext'],
            'iv' => $encrypted['iv'],
            'tag' => $encrypted['tag'],
        ]);

        $thread->update(['last_message_at' => now()]);

        return $message;
    }
}
