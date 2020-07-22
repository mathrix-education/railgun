<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateSlugPrevColumnOnChaptersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chapters', static function (Blueprint $table): void {
            $table->string('slug_prev')->nullable()->after('slug');
        });

        DB::table('chapters')->get(['id', 'slug'])->each(static function (stdClass $chapter): void {
            $newSlug = Str::of($chapter->slug)
                ->replace('_', '-')
                ->replaceMatches('/-+/', '-')
                ->slug();

            DB::table('chapters')->where('id', '=', $chapter->id)
                ->update(['slug' => $newSlug]);
        });

        DB::update('UPDATE `chapters` SET `slug_prev` = `slug`');
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
