<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpaceTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_translations', function (Blueprint $table) {
            $table->id();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->string('surface', 512)->nullable();
            $table->string('condition', 512)->nullable();
            $table->string('locale')->index();

            $table->foreignId('space_id')
                ->constrained()
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
        Schema::dropIfExists('space_translations');
    }
}
