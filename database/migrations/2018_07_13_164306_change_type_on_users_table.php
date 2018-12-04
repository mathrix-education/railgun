<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        DB::statement("UPDATE users SET `type` = 'registered' WHERE `type` NOT IN ('registered', 'moderator', 'admin')");
        DB::statement("ALTER TABLE users CHANGE `type` `type` ENUM('registered', 'moderator', 'admin')");
    }
}
