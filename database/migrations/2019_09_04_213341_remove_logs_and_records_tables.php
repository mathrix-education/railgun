<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLogsAndRecordsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        (new CreateRecordsTable())->down();
        (new CreateLogsTable())->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        (new CreateLogsTable())->up();
        (new CreateRecordsTable())->up();
    }
}
