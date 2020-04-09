<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Controllers;

use Illuminate\Support\Collection;
use Mathrix\Healthz\Checks\Check;
use Mathrix\Healthz\ChecksRegistry;
use Throwable;
use function microtime;
use function round;

/**
 * Controller to serve the checks route.
 */
class HealthzController
{
    /**
     * POST /healthz
     *
     * @param ChecksRegistry $checks The checks registry.
     *
     * @return Collection|mixed[]
     *
     * @noinspection PhpUnused
     */
    public function healthz(ChecksRegistry $checks): Collection
    {
        return $checks
            ->mapWithKeys(static function (Check $check) {
                $start = microtime(true);

                try {
                    $details = $check->execute();
                    $data    = ['status' => 'ok'];

                    if ($check->exposeDetails()) {
                        $data['details'] = $details;
                    }
                } catch (Throwable $exception) {
                    $data = [
                        'status'  => 'failure',
                        'message' => $exception->getMessage(),
                    ];
                } finally {
                    $data['duration'] = round((microtime(true) - $start) * 1000, 2);
                }

                return [$check->getName() => $data];
            })
            ->toBase();
    }
}
