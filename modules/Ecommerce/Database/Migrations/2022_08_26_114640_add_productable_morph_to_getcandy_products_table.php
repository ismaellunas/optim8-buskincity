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
        Schema::table(config('getcandy.database.table_prefix').'products', function (Blueprint $table) {
            $table->nullableMorphs('productable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('getcandy.database.table_prefix').'products', function (Blueprint $table) {
            $table->dropMorphs('productable');
        });
    }
};
