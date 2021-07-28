<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 15)->index();
            $table->string('title', 255);
            $table->text('excerpt')->nullable();
            $table->longText('data')->nullable();
            $table->string('slug', 255)->unique();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->foreignId('page_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique(['page_id', 'locale']);
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
        Schema::dropIfExists('page_translations');
    }
}
