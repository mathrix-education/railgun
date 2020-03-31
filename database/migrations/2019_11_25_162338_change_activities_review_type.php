<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeActivitiesReviewType extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activities', static function (Blueprint $table): void {
            $table->string('review')->after('reviewed');
            $table->dropColumn('reviewed');
        });

        DB::table('activities')->update(['review' => '{}']);

        DB::unprepared('ALTER TABLE activities MODIFY review JSON NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', static function (Blueprint $table): void {
            $table->boolean('reviewed')->after('premium');
            $table->dropColumn('review');
        });
    }
}
