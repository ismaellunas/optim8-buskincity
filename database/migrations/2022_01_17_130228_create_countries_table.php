<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string("alpha2", 2)->unique();
            $table->string("alpha3", 3)->unique();
            $table->string("display_name", 64)->nullable();
            $table->string("dial", 8)->nullable();
            $table->json("dials")->nullable();
            $table->string("currency_name", 64)->nullable();
            $table->string("currency_alphabetic_code", 16)->nullable();
            $table->string("currency_country_name", 64)->nullable();
            $table->string("continent", 8)->nullable();
            $table->string("tld", 8)->nullable();
            $table->json("languages")->nullable();
            $table->boolean("is_active")->default(true);
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
        Schema::dropIfExists('countries');
    }
}
