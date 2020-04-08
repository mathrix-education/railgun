<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Mathrix\Healthz\ChecksCollection;
use Throwable;
use function is_int;
use function is_string;
use function microtime;
use function round;

class HealthzController
{
    /**
     * POST /healthz
     *
     * @return Collection
     */
    public function healthz(ChecksCollection $checks): Collection
    {
        return $checks
            ->map(static function ($options, $check) {
                if (is_int($check) && is_string($options)) {
                    $check   = $options;
                    $options = [];
                }

                Container::getInstance()->when($check)->needs('$options')->give($options);

                return Container::getInstance()->make($check);
            })
            ->mapWithKeys(static function ($check) {
                $start = microtime(true);

                try {
                    $details = $check->execute();
                    $data    = ['status' => 'ok'];
                    if ($details) {
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

                return [$check::NAME => $data];
            })
            ->toBase();
    }
}
