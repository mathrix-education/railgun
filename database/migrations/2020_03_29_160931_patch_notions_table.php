<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Railgun\Models\Notion;

class PatchNotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notions', static function (Blueprint $table): void {
            $table->enum('type', Notion::TYPES)->default(Notion::TYPE_STANDARD)->after('id');
            $table->string('slug')->after('name')->nullable();
        });

        DB::table('notions')
            ->get(['id', 'name'])
            ->each(static function (stdClass $notion): void {
                DB::table('notions')->where('id', '=', $notion->id)
                    ->update(['slug' => Str::slug($notion->name)]);
            });

        DB::unprepared('alter table `notions` alter column `type` drop default');

        Schema::table('notions', static function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notions', static function (Blueprint $table): void {
            $table->dropColumn(['type', 'slug']);
        });
    }
}
