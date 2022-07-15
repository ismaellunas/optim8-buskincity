<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('address', 512)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->tinyInteger('depth')->default(0);
            $table->boolean('is_page_enabled')->default(false);

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('spaces')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
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
        Schema::dropIfExists('spaces');
    }
}
