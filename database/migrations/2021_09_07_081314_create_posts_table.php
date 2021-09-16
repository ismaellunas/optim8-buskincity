<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 15)->default('en');
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->dateTime('scheduled_at')->nullable();
            $table
                ->foreignId('cover_image_id')
                ->nullable()
                ->constrained('media')
                ->onUpdate('cascade')
                ->onDelete('set null');
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
        Schema::dropIfExists('posts');
    }
}
