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
        Schema::table('users', function (Blueprint $table) {
            $table->string('membre_titre')->nullable()->after('email');
            $table->string('membre_role')->nullable()->after('membre_titre');
            $table->string('photo')->nullable()->after('membre_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('membre_titre');
            $table->dropColumn('membre_role');
            $table->dropColumn('photo');
        });
    }
};
