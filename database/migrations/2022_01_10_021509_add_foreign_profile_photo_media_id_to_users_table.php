<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignProfilePhotoMediaIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_photo_path']);
            $table
                ->foreignId('profile_photo_media_id')
                ->nullable()
                ->constrained('media')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable();
            $table->dropForeign(['profile_photo_media_id']);
            $table->dropColumn('profile_photo_media_id');
        });
    }
}
