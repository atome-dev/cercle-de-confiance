<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('roles')
                ->where('name', 'membre')
                ->where('guard_name', 'web')
                ->update(['name' => 'parent', 'updated_at' => now()]);

            DB::table('roles')->insertOrIgnore([
                'name' => 'professeur',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::table('roles')
                ->where('name', 'parent')
                ->where('guard_name', 'web')
                ->update(['name' => 'membre', 'updated_at' => now()]);

            DB::table('roles')->where('name', 'professeur')->delete();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
