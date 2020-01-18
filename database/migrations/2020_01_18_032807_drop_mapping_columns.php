<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        (new CreateMarksTable())->down();

        Schema::table('comments', static function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
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
