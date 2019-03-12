<?php

namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Class CheckController
 *
 * @package App\Controllers
 */
class CheckController extends BaseController
{
    public function health()
    {
        // Database test
        $start = microtime(true);
        $dbStatus = "fail";
        try {
            $user = User::random();
            if ($user instanceof User) {
                $dbStatus = "ok";
            }
        } catch (\Exception $e) {
        }
        $end = (microtime(true) - $start) * 1000;

        $data = [
            "db" => [
                "type" => env("DB_CONNECTION"),
                "status" => $dbStatus,
                "latency_ms" => $end
            ]
        ];

        $response = new JsonResponse($data);

        return $response;
    }
}
