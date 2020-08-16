<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProtectedColumns extends Migration
{
    protected const TABLES = ['courses', 'subjects', 'exercises', 'flashcards', 'exams'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->boolean('protected')->after('premium')->default(false);
            });

            DB::statement("alter table `$table` alter column `protected` drop default");
        }

        $chapterIds = DB::table('chapters')
            ->whereNotNull('chapters.price')
            ->pluck('id')
            ->toArray();

        DB::table('courses')
            ->join('notions', 'courses.notion_id', '=', 'notions.id')
            ->whereIn('notions.chapter_id', $chapterIds)
            ->update(['protected' => true]);

        DB::table('subjects')
            ->join('notions', 'subjects.notion_id', '=', 'notions.id')
            ->whereIn('notions.chapter_id', $chapterIds)
            ->update(['protected' => true]);

        DB::table('exercises')
            ->join('subjects', 'exercises.subject_id', '=', 'subjects.id')
            ->where('subjects.protected', '=', true)
            ->update(['exercises.protected' => true]);

        DB::table('flashcards')
            ->whereIn('chapter_id', $chapterIds)
            ->update(['protected' => true]);

        DB::table('exams')
            ->whereIn('chapter_id', $chapterIds)
            ->update(['protected' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, static function (Blueprint $table): void {
                $table->dropColumn('protected');
            });
        }
    }
}
