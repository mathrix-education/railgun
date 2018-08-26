<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Score;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ScoreController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class ScoreController extends BaseController
{
    /**
     * @param string $interval
     *
     * @return Carbon
     * @throws Http400InvalidRequestException
     */
    private function getStartDate(string $interval): Carbon
    {
        $now = Carbon::now();
        switch ($interval) {
            case "year":
                return new Carbon("{$now->year}-01-01 00:00:00");
            case "month":
                return new Carbon("{$now->year}-{$now->month}-01 00:00:00");
            case "day":
                return new Carbon("{$now->year}-{$now->month}-{$now->day} 00:00:00");
            default:
                throw new Http400InvalidRequestException("`$interval`` is not a valid interval.");
        }
    }


    /**
     * Get the top scores for a given game
     *
     * @param string $game the game, such as `meteor`
     * @param string $interval The interval (allowed values: year, month, day)
     * @param int $count The number of high score to fetch
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function top(string $game, string $interval = "month", int $count = 20)
    {
        $scores = Score::with("user")
            ->whereBetween("created_at", [$this->getStartDate($interval), Carbon::now()])
            ->where("game", "=", $game)
            ->orderByDesc("score")
            ->limit($count)
            ->get();

        $response = new JsonResponse($scores);

        return $response;
    }


    /**
     * Generate a game token
     *
     * @param string $game
     *
     * @return JsonResponse
     */
    public function token(string $game): JsonResponse
    {
        $response = new JsonResponse([
            "token" => md5(Factory::create()->uuid . $game)
        ]);

        return $response;
    }


    /**
     * Register user high score
     *
     * @param Request $request
     * @param string $game
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function register(Request $request, string $game): JsonResponse
    {
        $signature = $request->input("signature");
        $score = $request->input("score", "no-score");
        $time = $request->input("time", "no-time");
        $token = $request->input("token");

        $expected = md5($score . $time . $token . $game);

        if ($expected !== $signature) {
            throw new Http401UnauthorizedException("Invalid signature");
        }

        $score = new Score([
            "game" => $game,
            "score" => $request->input("score"),
            "time" => $request->input("time"),
            "data" => $request->input("data"),
            "user_id" => $request->user()->id
        ]);
        $score->save();

        $response = new JsonResponse($score);
        return $response;
    }
}
