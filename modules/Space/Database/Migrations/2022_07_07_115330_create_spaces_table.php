<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

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
            $table->json('contacts')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->boolean('is_page_enabled')->default(false);
            $table->foreignId('type_id')
                ->nullable()
                ->constrained('global_options')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('page_id')
                ->nullable()
                ->constrained('pages')
                ->onUpdate('cascade')
                ->onDelete('set null');

            NestedSet::columns($table);

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
