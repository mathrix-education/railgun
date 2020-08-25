<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateThemesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('themes', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('order');
            $table->boolean('visible');
            $table->timestamps();

            // Foreign keys
            $table->foreignId('grade_id')->constrained();
            $table->foreignId('discipline_id')->constrained();
        });

        // Create themes
        DB::table('chapters')
            ->join('grades', 'chapters.grade_id', '=', 'grades.id')
            ->join('disciplines', 'chapters.discipline_id', '=', 'disciplines.id')
            ->groupBy(['grade_id', 'discipline_id'])
            ->selectRaw(implode(',', [
                "concat('Défaut (', grades.name, '/', disciplines.name, ')') as name",
                'grade_id',
                'discipline_id',
            ]))
            ->get()
            ->map(static function (stdClass $theme) {
                return array_merge((array)$theme, [
                    'slug'    => Str::of($theme->name)->replace('/', '-')->slug()->__toString(),
                    'order'   => 0,
                    'visible' => 1,
                ]);
            })
            ->tap(static function (Collection $themes): void {
                DB::table('themes')->insert($themes->toArray());
            });

        Schema::table('chapters', static function (Blueprint $table): void {
            $table->foreignId('theme_id')->nullable()->after('visible')->constrained();
        });

        DB::table('themes')
            ->get()
            ->each(static function (stdClass $theme): void {
                DB::table('chapters')
                    ->where([
                        'grade_id'      => $theme->grade_id,
                        'discipline_id' => $theme->discipline_id,
                    ])
                    ->update(['theme_id' => $theme->id]);
            });

        Schema::table('chapters', static function (Blueprint $table): void {
            $table->unsignedBigInteger('theme_id')->nullable(false)->change();
        });

        Schema::table('chapters', static function (Blueprint $table): void {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['discipline_id']);
            $table->dropColumn(['grade_id', 'discipline_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->foreignId('grade_id')->after('visible')->nullable()->constrained();
            $table->foreignId('discipline_id')->after('visible')->nullable()->constrained();
        });

        DB::table('themes')
            ->get()
            ->each(static function (stdClass $theme): void {
                DB::table('chapters')
                    ->where('theme_id', '=', $theme->id)
                    ->update([
                        'grade_id'      => $theme->grade_id,
                        'discipline_id' => $theme->discipline_id,
                    ]);
            });

        Schema::table('chapters', static function (Blueprint $table): void {
            $table->unsignedBigInteger('grade_id')->nullable(false)->change();
            $table->unsignedBigInteger('discipline_id')->nullable(false)->change();

            $table->dropForeign(['theme_id']);
            $table->dropColumn('theme_id');
        });

        Schema::dropIfExists('themes');
    }
}

