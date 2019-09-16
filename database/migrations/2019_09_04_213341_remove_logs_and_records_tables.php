<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class RemoveLogsAndRecordsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new CreateRecordsTable())->down();
        (new CreateLogsTable())->down();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        (new CreateLogsTable())->up();
        (new CreateRecordsTable())->up();
    }
}
