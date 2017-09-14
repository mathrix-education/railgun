<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('email');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('password');
            $table->enum('type', ['registered', 'premium', 'moderator', 'admin']);
            $table->string('token', 36);
            $table->integer('points')->default(0);
            $table->text('settings');
            $table->integer('class_id')->unsigned()->nullable();

            // Foreign keys
            $table->foreign('class_id')->references('id')->on('classes');
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
            $table->dropForeign('users_class_id_foreign');
        });

        Schema::dropIfExists('users');
    }
}
