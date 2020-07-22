<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddRedirectOnChaptersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->string('redirect')->nullable()->default(null)->after('slug');
        });

        DB::table('chapters')->get(['id', 'slug'])->each(static function (stdClass $chapter): void {
            $newSlug = Str::of($chapter->slug)
                ->replace('_', '-')
                ->replaceMatches('/-+/', '-')
                ->slug();

            DB::table('chapters')->where('id', '=', $chapter->id)
                ->update(['slug' => $newSlug]);
        });

        DB::update(<<<'SQL'
            update chapters
                inner join themes on chapters.theme_id = themes.id
                inner join grades on themes.grade_id = grades.id
                inner join disciplines on themes.discipline_id = disciplines.id
            set redirect = concat(grades.slug, '/', disciplines.slug, '/', chapters.slug)
            where chapters.id > 0
        SQL,
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->dropColumn('slug_prev');
        });
    }
}
