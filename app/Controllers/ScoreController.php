<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Score;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ScoreController.
 *
 * @author    Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ScoreController extends BaseController
{
    /**
     * @param string $interval
     *
     * @throws Http400InvalidRequestException
     *
     * @return Carbon
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
     * Get the top scores for a given game.
     *
     * @param string $game     the game, such as `meteor`
     * @param string $interval The interval (allowed values: year, month, day)
     * @param int    $count    The number of high score to fetch
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function top(string $game, string $interval = "month", int $count = 20): JsonResponse
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
     * Generate a game token.
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
     * Get scores around a certain value of score
     *
     * @param Request $request
     * @param string  $game
     * @param int     $score
     * @param int     $delta
     *
     * @return JsonResponse
     */
    public function around(Request $request, string $game, int $score, int $delta = 5): JsonResponse
    {
        $user = $request->user();

        $score = Score::where([
            ["game", "=", $game],
            ["score", "=", $score],
            ["user_id", "=", $user->id],
        ])
            ->orderByDesc("created_at")
            ->firstOrFail();

        $start = $score->rank - $delta;
        $end = $score->rank + $delta;

        $scores = Score::with(["user"])
            ->whereBetween("rank", [$start, $end])
            ->orderBy("rank")
            ->get();

        $response = new JsonResponse($scores);

        return $response;
    }

    /**
     * Get user personal best score
     *
     * @param Request $request
     * @param string  $game
     *
     * @return JsonResponse
     */
    public function personalBest(Request $request, string $game): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $score = Score::with(["user"])
            ->where([
                ["game", "=", $game],
                ["user_id", "=", $user->id]
            ])
            ->orderByDesc("score")
            ->first();

        $response = new JsonResponse($score ?? []);

        return $response;
    }

    /**
     * Register user high score.
     *
     * @param Request $request
     * @param string  $game
     *
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function register(Request $request, string $game): JsonResponse
    {
        $signature = $request->input("signature");
        $score = $request->input("score") ?? "no-score";
        $token = $request->input("token");

        $expected = md5($score . $token . $game);

        if ($expected !== $signature) {
            throw new Http401UnauthorizedException("Invalid signature");
        }

        $score = new Score([
            "game" => $game,
            "score" => $request->input("score"),
            "data" => $request->input("data"),
            "user_id" => $request->user()->id
        ]);
        $score->save();

        $score = Score::with(["user"])->findOrFail($score->id);

        $response = new JsonResponse($score);

        return $response;
    }
}
