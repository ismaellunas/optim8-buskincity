<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Base\Migration;

class FixLastFourOnTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->string('last_four', 4)->change();
        });
    }

    public function down()
    {
        if (! Str::startsWith(config('database.default'), 'pgsql')) {
            Schema::table($this->prefix.'transactions', function ($table) {
                $table->smallInteger('last_four')->unsigned()->change();
            });
        } else {
            DB::statement("ALTER TABLE {$this->prefix}transactions ALTER COLUMN last_four TYPE smallint USING last_four::smallint");
        }
    }
}
