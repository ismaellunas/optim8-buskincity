<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('key', 127)->unique();
            $table->string('name', 127)->nullable();
            $table->string('type', 32)->nullable();
            $table->integer('order')->default(1);
            $table->json('setting')->nullable();
            $table->timestamps();
        });

        Schema::table('field_groups', function (Blueprint $table) {
            $table->foreignId('form_id')
                ->constrained('forms')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('field_groups', function (Blueprint $table) {
            $table->dropForeign('form_id');
            $table->dropColumn('form_id');
        });

        Schema::dropIfExists('forms');
    }
};
