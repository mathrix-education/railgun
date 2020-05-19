<?php

declare(strict_types=1);

namespace Railgun\Collectors;

use Mathrix\Prometheus\Collectors\Collector;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Railgun\Support\PHPPMStatus;

class PHPPMCollector extends Collector
{
    private const PHPPM_WORKERS_TOTAL  = 'phppm_workers_total';
    private const PHPPM_REQUESTS_TOTAL = 'phppm_requests_total';

    /**
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function register(): void
    {
        $this->gauge(self::PHPPM_WORKERS_TOTAL, 'The total number php-pm workers.', ['status']);
        $this->gauge(self::PHPPM_REQUESTS_TOTAL, 'The total number of requests handled by php-pm', ['worker']);
    }

    /**
     * @param PHPPMStatus $data The PHP-PM status data.
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function compute(PHPPMStatus $data): void
    {
        foreach ($data->workers as $status => $total) {
            $this->gauge(self::PHPPM_WORKERS_TOTAL)->set($total, [$status]);
        }

        foreach ($data->handled_requests_per_worker as $worker => $total) {
            $this->gauge(self::PHPPM_REQUESTS_TOTAL)->set($total, [$worker]);
        }
    }
}
