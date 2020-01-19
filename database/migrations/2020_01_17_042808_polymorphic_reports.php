<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PolymorphicReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->unsignedBigInteger('reportable_id')->nullable()->after('module_id');
            $table->enum('reportable_type', ['course', 'exercise', 'flashcard', 'exam'])->after('module_id');
            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('reports', static function (Blueprint $table) {
            $table->dropIndex(['reportable_type', 'reportable_id']);
            $table->dropColumn(['reportable_type', 'reportable_id']);
        });
    }
}
