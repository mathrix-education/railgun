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
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->string('review')->after('reviewed');
            $table->dropColumn('reviewed');
        });

        DB::table('activities')->update(['review' => '{}']);

        DB::unprepared('ALTER TABLE activities MODIFY review JSON NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->boolean('reviewed')->after('premium');
            $table->dropColumn('review');
        });
    }
}
