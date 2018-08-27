<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Score;

/**
 * Class ScoreTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class ScoreTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Score::class,
            "count" => 300,
        ]);
    }
}
