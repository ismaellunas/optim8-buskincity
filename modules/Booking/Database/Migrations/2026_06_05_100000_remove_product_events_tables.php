<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'product_event_id')) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_product_event_id_foreign');
            } else {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropForeign(['product_event_id']);
                });
            }

            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('product_event_id');
            });
        }

        if (Schema::hasTable('schedules')) {
            DB::table('schedules')
                ->where('schedulable_type', 'Modules\\Booking\\Entities\\ProductEvent')
                ->delete();
        }

        Schema::dropIfExists('product_event_translations');
        Schema::dropIfExists('product_events');
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_events')) {
            Schema::create('product_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained(config('lunar.database.table_prefix').'products')->cascadeOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->dateTime('started_at');
                $table->dateTime('ended_at');
                $table->string('timezone')->nullable();
                $table->string('status', 20);
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country_code', 3)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_event_translations')) {
            Schema::create('product_event_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_event_id')->constrained('product_events')->cascadeOnDelete();
                $table->string('locale')->index();
                $table->text('description')->nullable();
                $table->string('excerpt')->nullable();
                $table->unique(['product_event_id', 'locale']);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('events') && ! Schema::hasColumn('events', 'product_event_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('product_event_id')
                    ->nullable()
                    ->after('order_line_id')
                    ->constrained('product_events')
                    ->nullOnDelete();
            });
        }
    }
};
