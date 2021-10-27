<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('Url');
            $table->string('url')->nullable();
            $table->integer('order')->default(1);
            $table->bigInteger('parent_id')->nullable();
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
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
        Schema::dropIfExists('menu_items');
    }
}
