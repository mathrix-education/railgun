<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteLevelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('level');
        });

        (new CreateLevelsTable())->down();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        (new CreateLevelsTable())->up();

        Schema::table('users', static function (Blueprint $table): void {
            $table->unsignedInteger('level')->after('token');
        });
    }
}
