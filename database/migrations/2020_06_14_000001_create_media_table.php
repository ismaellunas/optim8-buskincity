<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name', 191)->unique();
            $table->string('extension', 15);
            $table->string('file_type', 31);
            $table->string('version', 15)->nullable();
            $table->text('file_url');
            $table->unsignedBigInteger('size')->default(0);
            $table->json('assets')->nullable();
            $table->nullableMorphs('medially');
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
        Schema::dropIfExists('media');
    }
}
