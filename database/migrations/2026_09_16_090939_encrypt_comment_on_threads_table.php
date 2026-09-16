<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('comment');
        });

        Schema::table('threads', function (Blueprint $table) {
            // Chiffré comme les messages du dossier (AES-256-GCM, clé du dossier) —
            // pas l'enveloppe app utilisée pour le nom/l'email de l'expéditeur.
            $table->text('comment_ciphertext')->nullable()->after('school_class');
            $table->string('comment_iv', 32)->nullable()->after('comment_ciphertext');
            $table->string('comment_tag', 32)->nullable()->after('comment_iv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['comment_ciphertext', 'comment_iv', 'comment_tag']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('school_class');
        });
    }
};
