<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('lunar.database.table_prefix').'products_meta', function (Blueprint $table) {
            $table->id();

            $table->string('type')->default('null');
            $table->string('key')->index();
            $table->text('value')->nullable();

            $table
                ->foreignId('product_id')
                ->nullable()
                ->constrained(config('lunar.database.table_prefix').'products')
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
        Schema::dropIfExists(config('lunar.database.table_prefix').'products_meta');
    }
};
