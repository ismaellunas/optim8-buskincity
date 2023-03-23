<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_entries_meta', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('null');
            $table->string('key')->index();
            $table->text('value')->nullable();

            $table->foreignId('form_entry_id')
                ->constrained('form_entries')
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
        DB::statement("DROP TABLE form_entries_meta CASCADE");
    }
};
