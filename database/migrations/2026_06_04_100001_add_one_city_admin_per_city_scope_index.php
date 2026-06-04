<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OQ3: at most one city_administrator scope row per city (replace on approval).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_scope')) {
            return;
        }

        $role = config('permission.role_names.city_admin', 'city_administrator');

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS user_scope_one_city_admin_per_city
            ON user_scope (scope_id)
            WHERE role = '{$role}' AND scope_type = 'city'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS user_scope_one_city_admin_per_city');
    }
};
