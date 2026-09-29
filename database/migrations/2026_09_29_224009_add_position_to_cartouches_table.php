<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cartouches', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('id');
        });

        // Keep the current homepage order, which was by id.
        foreach (DB::table('cartouches')->orderBy('id')->pluck('id') as $index => $id) {
            DB::table('cartouches')->where('id', $id)->update(['position' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cartouches', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
