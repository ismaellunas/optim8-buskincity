<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_applications', function (Blueprint $table) {
            $table->foreignId('country_space_id')
                ->nullable()
                ->after('city_id')
                ->constrained('spaces')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('role_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_space_id');
        });
    }
};
