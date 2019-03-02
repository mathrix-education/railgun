<?php

use App\Models\Score;
use Illuminate\Support\Facades\DB;

/**
 * Class ScoreTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 */
class ScoreTableSeeder extends BaseTableSeeder
{
    const SCORE_COUNT = 300;

    public function run()
    {
        $this->seedFromFactory(Score::class, 300);

        $data = DB::select("SELECT DISTINCT game FROM scores GROUP BY game");
        $games = array_map(function ($v) {
            return $v->game;
        }, $data);


        $this->output->writeln("<comment>Generating:</comment> score ranks");
        $this->output->progressStart(self::SCORE_COUNT);

        foreach ($games as $game) {
            $scores = Score::where("game", "=", $game)
                ->orderByDesc("score")
                ->orderBy("created_at")
                ->get();
            $rank = 1;

            foreach ($scores as $score) {
                DB::table("scores")->where("id", "=", $score->id)
                    ->update(["rank" => $rank]);
                $rank++;
                $this->output->progressAdvance();
            }
        }
        $this->output->progressFinish();
        $this->output->writeln("<info>Generated:</info>  score ranks");
    }
}
