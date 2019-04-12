<?php

namespace App\Console\Commands;

use Illuminate\Database\MySqlConnection;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class DBDestroyCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class DBDestroyCommand extends BaseCommand
{
    protected $signature = "db:destroy";


    public function handle()
    {
        /** @var MySqlConnection $connection */
        $connection = app()->make("db")->connection("mysql");
        $schemaBuilder = $connection->getSchemaBuilder();
        $schemaBuilder->dropAllTables();
    }
}
