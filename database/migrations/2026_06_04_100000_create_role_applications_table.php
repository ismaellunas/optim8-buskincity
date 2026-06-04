<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('requested_role');
            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->foreignId('logo_media_id')
                ->nullable()
                ->constrained('media')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->json('branding')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->foreignId('replaced_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'requested_role']);
            $table->index(['city_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_applications');
    }
};
