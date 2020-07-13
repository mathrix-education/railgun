<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DropSlugs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notions', static function (Blueprint $table): void {
            $table->dropColumn('slug');
        });

        Schema::table('themes', static function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', static function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('themes')->select(['id', 'name'])->get()
            ->each(static function (stdClass $theme): void {
                DB::table('themes')->where('id', '=', $theme->id)
                    ->update(['slug' => Str::slug($theme->name)]);
            });

        Schema::table('themes', static function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('notions', static function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('notions')->select(['id', 'name'])->get()
            ->each(static function (stdClass $notion): void {
                DB::table('notions')->where('id', '=', $notion->id)
                    ->update(['slug' => Str::slug($notion->name)]);
            });

        Schema::table('notions', static function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
        });
    }
}
