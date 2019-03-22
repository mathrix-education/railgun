<?php

namespace App\Console\Commands\Migrations;

use App\Console\Commands\BaseCommand;
use Illuminate\Support\Facades\DB;

/**
 * Class PyramidReset.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidResetCommand extends BaseCommand
{
    protected $signature = "pyramid:reset";


    public function handle()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS=0");
        DB::statement("TRUNCATE TABLE class_discipline");
        DB::statement("TRUNCATE TABLE chapter_notion");
        DB::statement("TRUNCATE TABLE activity_notion");
        DB::statement("TRUNCATE TABLE activity_module");
        DB::statement("TRUNCATE TABLE countries");
        DB::statement("TRUNCATE TABLE classes");
        DB::statement("TRUNCATE TABLE disciplines");
        DB::statement("TRUNCATE TABLE chapters");
        DB::statement("TRUNCATE TABLE notions");
        DB::statement("TRUNCATE TABLE activities");
        DB::statement("TRUNCATE TABLE modules");
        DB::statement("SET FOREIGN_KEY_CHECKS=1");
        $this->call("db:seed", ["--class" => "ContentTableSeeder"]);
    }
}
