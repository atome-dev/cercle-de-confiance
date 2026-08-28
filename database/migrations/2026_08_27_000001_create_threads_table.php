<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique();
            $table->enum('recipient_type', ['group', 'member']);
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['nouveau', 'en_cours', 'archive'])->default('nouveau');

            $table->string('sender_name');
            $table->string('sender_email');
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_anonymous')->default(true);

            // Clé du dossier chiffrée avec APP_KEY (accès membre connecté)
            $table->text('thread_key_envelope');

            // Clé du dossier chiffrée avec une clé dérivée du code privé EFGH (accès anonyme)
            $table->text('anon_key_envelope')->nullable();

            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
