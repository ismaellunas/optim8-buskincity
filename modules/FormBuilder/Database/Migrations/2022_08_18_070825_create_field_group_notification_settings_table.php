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
        Schema::create('field_group_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 127);
            $table->json('send_to');
            $table->string('from_name', 127)->nullable();
            $table->string('from_email', 127)->nullable();
            $table->string('reply_to', 127)->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject');
            $table->text('message')->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreignId('field_group_id')
                ->constrained('field_groups')
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
        Schema::dropIfExists('field_group_notification_settings');
    }
};
