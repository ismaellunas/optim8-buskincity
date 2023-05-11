<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('unique_key', 16)->unique();
            $table->string('first_name', 128);
            $table->string('last_name', 128);
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->boolean('is_suspended')->default('false');
            $table->softDeletes();
            $table->timestamps();
        });

        /**
         * References
         * @link https://halimsamy.com/sql-soft-deleting-and-unique-constraint
         * @link https://www.youtube.com/watch?v=fqfoiiqPuMo
         */
        if (Str::startsWith(config('database.default'), 'pgsql')) {
            DB::statement('CREATE UNIQUE INDEX users_email_unique on users(email) WHERE deleted_at IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Str::startsWith(config('database.default'), 'pgsql')) {
            Schema::dropIfExists('users');
        } else {
            DB::statement("DROP TABLE IF EXISTS users CASCADE");
        }
    }
}
