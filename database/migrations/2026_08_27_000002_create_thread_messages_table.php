<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();

            $table->enum('author_type', ['sender', 'member']);
            $table->foreignId('author_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Contenu chiffré (AES-256-GCM manuel)
            $table->text('ciphertext');
            $table->string('iv', 32); // 16 bytes hex
            $table->string('tag', 32); // 16 bytes hex (GCM auth tag)

            $table->timestamps();

            $table->index('thread_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_messages');
    }
};
