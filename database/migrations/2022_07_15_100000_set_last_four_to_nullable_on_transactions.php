<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetLastFourToNullableOnTransactions extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            if (config('database.default') == 'pgsql') {
                DB::statement(
                    'ALTER TABLE '.$this->prefix.'transactions '.
                    'ALTER COLUMN last_four TYPE smallint USING last_four::smallint'
                );
                DB::statement(
                    'ALTER TABLE '.$this->prefix.'transactions '.
                    'ALTER COLUMN last_four DROP NOT NULL'
                );
            } else {
                $table->smallInteger('last_four')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'transactions', function ($table) {
            $table->smallInteger('last_four')->nullable(false)->change();
        });
    }
}
