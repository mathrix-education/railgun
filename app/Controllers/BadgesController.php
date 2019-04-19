<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class BadgesController.
 * Various methods to generate badges with https://shields.io/.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc2
 */
class BadgesController extends BaseController
{
    /**
     * Get color give a percentage.
     *
     * @param string $percentage
     *
     * @return string
     */
    private function getColor($percentage)
    {
        if ($percentage > 50) {
            return "brightgreen";
        } else if ($percentage > 40) {
            return "yellow";
        } else if ($percentage > 30) {
            return "orange";
        } else {
            return "red";
        }
    }


    /**
     * GET /badges/coverage
     * Get the coverage results.
     *
     * @return JsonResponse
     */
    public function version()
    {
        return new JsonResponse([
            "schemaVersion" => 1,
            "label" => "Version",
            "color" => "blue",
            "message" => env("APP_VERSION") . " - " . env("APP_BUILD")
        ]);
    }


    /**
     * GET /badges/coverage
     * Get the application version results. This method is ignored by the tests since it display information about them.
     *
     * @return JsonResponse
     * @codeCoverageIgnore
     */
    public function coverage()
    {
        $textReport = app()->basePath("artifacts/coverage.txt");
        $message = "Unknown";
        $color = "lightgrey";

        if (file_exists($textReport)) {
            $data = file_get_contents($textReport);
            preg_match("/Lines:\s+(\d{2}\.\d)/", $data, $matches);

            if (!empty($matches[1]) && is_numeric($matches[1])) {
                $coverageScore = (float)$matches[1];
                $color = $this->getColor($coverageScore);
                $message = "$coverageScore%";
            }
        }

        $data = [
            "schemaVersion" => 1,
            "label" => "Coverage",
            "color" => $color,
            "message" => $message
        ];

        return new JsonResponse($data);
    }
}
