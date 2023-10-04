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
            $table->string('timezone', 32)->nullable();
            $table->boolean('is_same_address_as_parent')->default(true);
            $table->string('city')->nullable();
            $table->string('country_code', 3)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->tinyInteger('status')->default(0);

            $table
                ->foreignId('space_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
