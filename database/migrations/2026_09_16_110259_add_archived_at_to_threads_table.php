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
            // Horodatage du dernier passage au statut "archive" — sert de point de
            // départ à la purge automatique (voir PurgeArchivedThreads), distinct de
            // updated_at qui bouge pour n'importe quelle modification du dossier.
            $table->timestamp('archived_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
