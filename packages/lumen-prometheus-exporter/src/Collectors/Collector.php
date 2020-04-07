<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

use Illuminate\Container\Container;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use function config;
use function is_string;

/**
 * Base class for writing collectors.
 *
 * @method void register() Register the collector metrics.
 * @method void compute() Compute the collector metrics.
 */
abstract class Collector
{
    /** @var CollectorRegistry $registry The CollectorRegistry which holds the metrics. */
    protected CollectorRegistry $registry;

    public function __construct()
    {
        $this->registry = Container::getInstance()->get(CollectorRegistry::class);
    }

    /**
     * @return string The Prometheus namespace.
     */
    protected function getNamespace(): string
    {
        return config('prometheus.namespace');
    }

    /**
     * Register or get a Prometheus {@see Counter}.
     *
     * @param string      $name   The counter name.
     * @param string|null $help   The counter help text.
     * @param string[]    $labels The counter labels.
     *
     * @return Counter The Prometheus {@see Counter}.
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function counter(string $name, ?string $help = null, array $labels = []): Counter
    {
        if (is_string($help)) {
            return $this->registry->getOrRegisterCounter($this->getNamespace(), $name, $help, $labels);
        }

        return $this->registry->getCounter($this->getNamespace(), $name);
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
    public function gauge(string $name, ?string $help = null, array $labels = []): Gauge
    {
        if (is_string($help)) {
            return $this->registry->getOrRegisterGauge($this->getNamespace(), $name, $help, $labels);
        }

        return $this->registry->getGauge($this->getNamespace(), $name);
    }

    /**
     * Register or get a Prometheus {@see Histogram}.
     *
     * @param string      $name    The histogram name.
     * @param string|null $help    The histogram help text.
     * @param string[]    $labels  The histogram labels.
     * @param int[]|null  $buckets The histogram buckets.
     *
     * @return Histogram The Prometheus {@see Histogram}.
     *
     * @throws MetricsRegistrationException
     * @throws MetricNotFoundException
     */
    public function histogram(
        string $name,
        ?string $help = null,
        array $labels = [],
        ?array $buckets = null
    ): Histogram
    {
        if (is_string($help)) {
            return $this->registry->getOrRegisterHistogram($this->getNamespace(), $name, $help, $labels, $buckets);
        }

        return $this->registry->getHistogram($this->getNamespace(), $name);
    }
}
