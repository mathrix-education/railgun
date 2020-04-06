<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

use Illuminate\Container\Container;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use function config;
use function is_string;

/**
 * Base class for writing collectors.
 *
 * @method register
 */
abstract class Collector
{
    /** @var CollectorRegistry $registry The CollectorRegistry which holds the metrics. */
    private CollectorRegistry $registry;

    public function __construct()
    {
        $this->registry = Container::getInstance()->get(CollectorRegistry::class);
    }

    /**
     * Register or get a Prometheus {@see Gauge}.
     *
     * @param string      $name   The gauge name.
     * @param string|null $help   The gauge help text.
     * @param string[]    $labels The gauge labels.
     *
     * @return Gauge The Prometheus {@see Gauge}.
     *
     * @throws MetricsRegistrationException|MetricNotFoundException
     */
    protected function gauge(string $name, ?string $help = null, array $labels = []): Gauge
    {
        if (is_string($help)) {
            return $this->registry->getOrRegisterGauge(config('prometheus.namespace'), $name, $help, $labels);
        }

        return $this->registry->getGauge(config('prometheus.namespace'), $name);
    }

    /**
     * Register or get a Prometheus {@see Gauge}.
     *
     * @param string      $name    The histogram name.
     * @param string|null $help    The histogram help text.
     * @param string[]    $labels  The histogram labels.
     * @param int[]|null  $buckets The histogram buckets.
     *
     * @throws MetricsRegistrationException
     */
    protected function histogram(
        string $name,
        ?string $help,
        array $labels = [],
        ?array $buckets = null
    ): void
    {
        $this->registry->getOrRegisterHistogram();
    }
}
