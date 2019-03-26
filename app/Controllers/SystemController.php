<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Mathrix\Lumen\Bases\BaseController;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class SystemController.
 * Allow control of system services.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SystemController extends BaseController
{
    /**
     * GET /
     * The base application route.
     *
     * @return JsonResponse
     */
    public function base(): JsonResponse
    {

    }


    /**
     * GET /system/health
     * Health check for Docker
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
    }


    /**
     * POST /system/sitemap
     * Regenerate sitemap.
     *
     * @return JsonResponse
     */
    public function sitemap()
    {
        $command = "sitemap";
        $output = new BufferedOutput();

        $exitCode = Artisan::call($command, [], $output);
        $data = [
            "command" => $command,
            "exit" => $exitCode,
            "logs" => collect(explode("\n", $output->fetch()))
                ->map(function ($line) {
                    return trim($line);
                })
                ->filter(function ($line) {
                    return !empty($line);
                })
                ->values()
        ];

        return new JsonResponse($data);
    }
}
