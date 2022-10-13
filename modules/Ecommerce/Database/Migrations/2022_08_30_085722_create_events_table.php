<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('booked_at');
            $table->string('timezone', 32)->default('UTC');
            $table->unsignedSmallInteger('duration')->default(0);
            $table->string('duration_unit', 15)->default('minute');
            $table->string('status', 15);
            $table->string('message', 500)->nullable();

            $table
                ->foreignId('schedule_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreignId('order_line_id')
                ->nullable()
                ->constrained(config('getcandy.database.table_prefix').'order_lines')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
