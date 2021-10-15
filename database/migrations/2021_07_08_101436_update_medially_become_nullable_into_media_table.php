<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateMediallyBecomeNullableIntoMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('medially_type', 255)->nullable()->change();
            $table->foreignId('medially_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
        try {
            Schema::table('media', function (Blueprint $table) {
                $table->string('medially_type', 255)->nullable(false)->change();
                $table->foreignId('medially_id')->nullable(false)->change();
            });
            DB::commit();
        } catch (QueryException $e) {
            if ($e->getCode() != 23502) {
                throw $e;
            } else {
                DB::rollBack();
            }
        }
    }
}
