<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Controllers;

use Illuminate\Container\BoundMethod;
use Illuminate\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Laravel\Lumen\Routing\Controller;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use function method_exists;

/**
 * Expose the Prometheus metrics route.
 */
class PrometheusController extends Controller
{
    private CollectorRegistry $registry;
    /** @var Collection|string[] $collectors The registered collectors. */
    private Collection $collectors;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry   = $registry;
        $this->collectors = Container::getInstance()->get('prometheus.collectors');
    }

    /**
     * GET /metrics
     * Expose the Prometheus metrics.
     */
    public function metrics(): Response
    {
        // Trigger all collectors computation
        $this->collectors->each(static function (string $collector): void {
            if (method_exists($collector, 'compute')) {
                // Allow compute method to use dependency injection
                BoundMethod::call(Container::getInstance(), "$collector@compute");
            }
        });

        $renderer = new RenderTextFormat();
        $content  = $renderer->render($this->registry->getMetricFamilySamples());

        return new Response($content, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ]);
    }
}
