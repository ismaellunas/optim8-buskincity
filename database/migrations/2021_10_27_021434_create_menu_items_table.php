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
            $table->string('title');
            $table->string('type', 18)->default('url');
            $table->string('url')->nullable();
            $table->integer('order')->default(1);
            $table->string('icon', 100)->nullable();
            $table->boolean('is_blank')->default(false);
            $table->bigInteger('parent_id')->nullable();
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->bigInteger('menu_itemable_id')->unsigned()->nullable();
            $table->string('menu_itemable_type')->nullable();
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
