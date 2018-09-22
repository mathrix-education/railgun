<?php

use Illuminate\Database\Migrations\Migration;

class ChangeTypeOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE users CHANGE `type` `type` VARCHAR(255)");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("ALTER TABLE users CHANGE `type` `type` ENUM('registered', 'moderator', 'admin')");
    }
}
