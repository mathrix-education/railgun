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
     * POST /system/sitemaps
     *
     * @return JsonResponse
     */
    public function sitemaps()
    {
        $command = "sitemaps:all";
        $output = new BufferedOutput();

        $exitCode = Artisan::call("sitemaps:all", [], $output);
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
