<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('translations', function(Blueprint $table)
        {
	    $table->collation = 'utf8mb4_bin';
            $table->bigIncrements('id');
            $table->integer('status')->default(1);
            $table->string('locale', 15);
            $table->string('group', 127)->nullable();
            $table->text('key');
            $table->text('value')->nullable();
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
        Schema::dropIfExists('translations');
	}

}
