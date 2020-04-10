<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Railgun\Models\Notion;

class AddTypeToNotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notions', static function (Blueprint $table): void {
            $table->enum('type', Notion::TYPES)->default(Notion::TYPE_STANDARD)->after('id');
        });

        DB::unprepared('alter table `notions` alter column `type` drop default');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notions', static function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
}
