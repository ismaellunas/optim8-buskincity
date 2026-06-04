<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical physical locations (OQ8/OQ9).
 *
 * Locations belong to a relational `cities` row — not the Space tree.
 * Optional `space_id` links a legacy Space row during transition.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('locations')) {
            return;
        }

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name', 128);
            $table->string('address', 500)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->foreignId('space_id')
                ->nullable()
                ->constrained('spaces')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
