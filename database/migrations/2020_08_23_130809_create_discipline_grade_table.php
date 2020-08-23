<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class CreateDisciplineGradeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discipline_grade', static function (Blueprint $table): void {
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('discipline_id');
            $table->primary(['grade_id', 'discipline_id']);
            $table->unsignedSmallInteger('order');
            $table->boolean('visible');

            // Foreign keys
            $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->onDelete('cascade');
            $table->foreign('discipline_id')
                ->references('id')
                ->on('disciplines')
                ->onDelete('cascade');
        });

        collect()
            ->merge(
                DB::table('themes')
                ->select(['grade_id', 'discipline_id', 'visible'])
                ->groupBy(['grade_id', 'discipline_id'])
                ->get()
            ->each(static function (stdClass $disciplineGrade): void {
                DB::table('discipline_grade')
                    ->insert([
                        'grade_id'      => $disciplineGrade->grade_id,
                        'discipline_id' => $disciplineGrade->discipline_id,
                        'visible'       => $disciplineGrade->visible,
                        'order'         =>
                    ]);
            })
            );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_grade');
    }
}
