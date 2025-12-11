<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('country_code', 3);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('state_code', 10)->nullable();
            $table->timestamps();

            $table->unique(['name', 'country_code']);
            $table->index('country_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
};
