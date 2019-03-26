<?php

namespace App\Console\Commands\Pyramid;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidForeignKeys.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidForeignKeysCommand extends BaseCommand
{
    protected $signature = "pyramid:foreign-keys";


    public function handle()
    {
        $this->line("Migrating classes table => grades table");
        DB::statement("INSERT grades SELECT * FROM classes");
        $this->success("Migrated grades table");


        $this->line("Migrating classes class_discipline => discipline_grade table...");
        DB::statement("INSERT discipline_grade " .
            "SELECT `id`, `discipline_id`, `class_id`, `visible`, `order` FROM class_discipline");
        $this->success("Migrated discipline_grade table");


        $this->line("Migrating class_id => grade_id on users table...");
        DB::statement("UPDATE users SET grade_id = class_id;");
        $this->success("Migrated grade_id on users table");


        $this->line("Migrating class_id => grade_id on chapters table...");
        DB::statement("UPDATE chapters SET grade_id = class_id;");
        $this->success("Migrated grade_id on chapters table");
    }
}
