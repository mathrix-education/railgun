<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::table('users', static function (Blueprint $table) : void {
            $table->string('password')->nullable()->change();
            $table->json('metadata')->nullable()->after('settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('users', static function (Blueprint $table) : void {
            $table->dropColumn('metadata');
            $table->string('password')->nullable(false)->change();
        });
    }
}
