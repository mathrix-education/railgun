<?php

use App\Models\Score;

/**
 * Class ScoreTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 */
class ScoreTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromFactory(Score::class, 300);

        // Then, compute their rank
        DB::unprepared('
        SET
            @row_number = 0;
        UPDATE
            scores,
            (
                SELECT
                    id,
                    (@row_number: = @row_number + 1) AS `rank`
                FROM
                    scores
            ) AS computed
        SET
            scores.rank = computed.rank
        WHERE
            scores.id = computed.id
        ');
    }
}
