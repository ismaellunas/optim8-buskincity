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
        Schema::create('order_check_ins', function (Blueprint $table) {
            $table->id();
            $table->dateTime('checked_in_at');
            $table->string('type', 15);
            $table->jsonb('geolocation');
            $table->json('data');
            $table->boolean('is_allowed')->default(false);

            $table
                ->foreignId('order_id')
                ->constrained(config('lunar.database.table_prefix').'orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreignId('user_id')
                ->constrained('users')
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
        Schema::dropIfExists('order_check_ins');
    }
};
