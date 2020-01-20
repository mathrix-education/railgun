<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropMappingColumns extends Migration
{
    private const MAPPINGS_TABLE = [
        'exams',
        'exercises',
        'flashcards',
        'lessons',
        'packs',
        'sections',
        'trainings',
        'subjects',
        'courses',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        collect(self::MAPPINGS_TABLE)->each(static function (string $table) {
            Schema::table($table, static function (Blueprint $table) {
                $table->dropColumn('mapping');
            });
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['points', 'daily', 'daily_achievements']);
        });

        (new CreateMarksTable())->down();

        Schema::table('categories', static function (Blueprint $table) {
            $table->dropColumn(['image']);
        });

        Schema::table('chapters', static function (Blueprint $table) {
            $table->dropColumn(['notion_count', 'activity_count']);
        });

        Schema::table('notions', static function (Blueprint $table) {
            $table->dropColumn(['activity_count']);
        });

        Schema::table('comments', static function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn(['module_id', 'deleted_at']);
        });

        Schema::table('posts', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });

        Schema::table('reports', static function (Blueprint $table) {
            $table->dropColumn('context');
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });

        (new CreatePurchasesTable())->down();
        (new CreateTransactionsTable())->down();
        (new CreateJobsTable())->down();
        (new CreateProductProductTable())->down();
        (new CreateDiscountProductTable())->down();
        (new CreateDiscountsTable())->down();
        (new CreateProductsTable())->down();
        (new CreateModulesTable())->down();
        (new CreateActivitiesTable())->down();

        DB::unprepared('alter table comments modify commentable_id bigint unsigned not null');
        DB::unprepared('alter table reports modify reportable_id bigint unsigned not null');

        Schema::dropIfExists('notifications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //        collect(self::MAPPINGS_TABLE)->each(static function (string $table) {
        //            Schema::table($table, static function (Blueprint $table) {
        //                $table->string('mapping');
        //            });
        //        });
    }
}
