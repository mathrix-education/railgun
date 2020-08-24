<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
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

            $table->primary(['grade_id', 'discipline_id']);
            $table->unsignedSmallInteger('order');
            $table->boolean('visible');

            // Foreign keys
            $table->foreignId('grade_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('discipline_id')
                ->constrained()
                ->onDelete('cascade');
        });

        DB::table('themes')
            ->selectRaw('grade_id, discipline_id, max(visible) as visible')
            ->groupBy(['grade_id', 'discipline_id'])
            ->orderByRaw('grade_id, discipline_id')
            ->get()
            ->map(static fn(stdClass $line) => array_merge((array)$line, ['visible' => true]))
            ->pipe(static function (Collection $lines) {
                $iterator = 0;
                $gradeId  = null;

                return $lines->map(static function ($line) use (&$iterator, &$gradeId) {
                    if ($line['grade_id'] !== $gradeId) {
                        $gradeId  = $line['grade_id'];
                        $iterator = 0;
                    }

                    $line['order'] = $iterator++;

                    return $line;
                });
            })
            ->tap(static function (Collection $lines): void {
                DB::table('discipline_grade')->insert($lines->toArray());
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_grade');
    }
}
