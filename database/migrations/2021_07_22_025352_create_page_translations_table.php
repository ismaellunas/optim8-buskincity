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
            $table->string('unique_key', 16)->unique();
            $table->string('locale', 15)->index();
            $table->string('title', 255);
            $table->text('excerpt')->nullable();
            $table->longText('data');
            $table->string('slug', 255);
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('generated_style')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('page_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique(['locale', 'slug']);
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
