<?php

declare(strict_types=1);

namespace Railgun\Collectors;

use Mathrix\Prometheus\Collectors\Collector;
use Railgun\Support\PHPPMStatus;
use function array_key_exists;

class PHPPMCollector extends Collector
{
    private const PHPPM_WORKERS_TEMPLATE = 'phppm_workers_';
    private const PHPPM_WORKERS_METRICS  = ['total', 'ready', 'busy', 'created', 'registered', 'closed'];
    private const PHPPM_REQUESTS_TOTAL   = 'phppm_requests_total';

    /**
     * Get the metric full qualified name.
     *
     * @param string $metric The PHPPM worker metric name.
     *
     * @return string The metric full qualified name.
     */
    private function getName(string $metric): string
    {
        $name = $metric === 'total' ? 'total' : "{$metric}_total";

        return self::PHPPM_WORKERS_TEMPLATE . $name;
    }

    public function register(): void
    {
        foreach (self::PHPPM_WORKERS_METRICS as $metric) {
            $help = $metric === 'total'
                ? 'The total number php-pm workers.'
                : "The total number of $metric php-pm workers.";

            $this->gauge($this->getName($metric), $help);
        }

        $this->gauge(self::PHPPM_REQUESTS_TOTAL, 'The total number of requests handled by php-pm');
    }

    public function compute(PHPPMStatus $status): void
    {
        foreach (self::PHPPM_WORKERS_METRICS as $metric) {
            if (array_key_exists($metric, $status->workers)) {
                $this->gauge($this->getName($metric))->set($status->workers[$metric]);
            }
        }

        $this->gauge(self::PHPPM_REQUESTS_TOTAL)->set($status->handled_requests);
    }
}
