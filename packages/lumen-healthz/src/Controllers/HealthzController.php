<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Healthz\Checks\Check;
use Mathrix\Healthz\ChecksRegistry;
use Throwable;
use function explode;
use function in_array;
use function microtime;
use function round;

/**
 * Controller to serve the checks route.
 */
class HealthzController
{
    /**
     * GET /healthz
     *
     * @param Request        $request The Illuminate HTTP request.
     * @param ChecksRegistry $checks  The checks registry.
     *
     * @return JsonResponse The checks responses.
     *
     * @noinspection PhpUnused
     */
    public function healthz(Request $request, ChecksRegistry $checks): JsonResponse
    {
        /** @var string[] $only */
        $only   = explode(',', $request->query('checks', 'all'));
        $status = 200;

        $data = $checks
            ->filter(static function (Check $check) use ($only) {
                return in_array('all', $only, true) ? true : in_array($check->getName(), $only, true);
            })
            ->mapWithKeys(static function (Check $check) use (&$status) {
                $start = microtime(true);

                try {
                    $details = $check->execute();
                    $data    = ['status' => 'ok'];

                    if ($check->exposeDetails()) {
                        $data['details'] = $details;
                    }
                } catch (Throwable $exception) {
                    $data   = [
                        'status'  => 'failure',
                        'message' => $exception->getMessage(),
                    ];
                    $status = 500;
                } finally {
                    $data['duration'] = round((microtime(true) - $start) * 1000, 2);
                }

                return [$check->getName() => $data];
            })
            ->toBase();

        return new JsonResponse($data, $status);
    }
}
