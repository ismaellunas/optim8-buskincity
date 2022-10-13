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
        Schema::create('schedule_rules', function (Blueprint $table) {
            $table->id();
            $table->date('started_date')->nullable();
            $table->date('ended_date')->nullable();
            $table->tinyInteger('day')->nullable();
            $table->string('type', 16);
            $table->boolean('is_available')->default(false);
            $table
                ->foreignId('schedule_id')
                ->constrained('schedules')
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
        Schema::dropIfExists('schedule_rules');
    }
};
