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
        Schema::create('space_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('address')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at');

            $table->nullableMorphs('eventable');
            $table
                ->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

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
        Schema::dropIfExists('space_events');
    }
};
