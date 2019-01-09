<?php
namespace App\Observers;

use App\Models\Score;
use Illuminate\Support\Facades\DB;

/**
 * Class ScoreObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
 */
class ScoreObserver extends BaseObserver
{
    /**
     * @param Score $score
     *
     * @return bool
     */
    public function saving($score)
    {
        $nearestScore = Score::where([
            ["score", ">=", $score->score],
            ["game", "=", $score->game]
        ])
            ->orderBy("score")
            ->orderByDesc("id")
            ->first();

        if ($nearestScore === null) {
            // This is the first score
            $score->rank = 1;
        } else {
            $score->rank = $nearestScore->rank + 1;
        }

        return true;
    }

    /**
     * @param Score $score
     *
     * @return bool
     */
    public function saved($score) {
        DB::update("UPDATE scores SET scores.rank = scores.rank + 1 WHERE scores.game = ? AND scores.score < ?", [
            $score->game,
            $score->score
        ]);

        return true;
    }
}
