<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrozenAtOnContentTables extends Migration
{
    private const TABLES = ['courses', 'exercises', 'flashcards', 'exams'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->timestamp('frozen_at')->nullable()->after('premium');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $tableName) {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->dropColumn('frozen_at');
            });
        }
    }
}
