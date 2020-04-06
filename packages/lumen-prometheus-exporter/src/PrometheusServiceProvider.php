<?php

declare(strict_types=1);

namespace Mathrix\Prometheus;

use Illuminate\Config\Repository;
use Illuminate\Container\BoundMethod;
use Illuminate\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Mathrix\Prometheus\Controllers\PrometheusController;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use RuntimeException;
use function array_key_exists;
use function collect;
use function dirname;
use function is_string;
use function sprintf;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/prometheus.php', 'prometheus');

        /** @var Repository $config */
        $config = $this->app['config'];

        // Register the Collector registry
        $this->app->singleton(CollectorRegistry::class, function () use ($config): CollectorRegistry {
            switch ($config->get('prometheus.adapter')) {
                case 'redis':
                    $adapter = $this->getRedisAdapter();
                    break;
                case 'memory':
                default:
                    $adapter = new InMemory();
                    break;
            }

            return new CollectorRegistry($adapter);
        });

        // Register the collector metrics
        $this->app->singleton('prometheus.collectors', static function () use ($config): Collection {
            return collect($config->get('prometheus.collectors', []))
                ->each(static function (string $collector): void {
                    BoundMethod::call(Container::getInstance(), "$collector@register");
                });
        });

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->get('/metrics', PrometheusController::class . '@metrics');
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [CollectorRegistry::class, 'prometheus.collectors'];
    }

    /**
     * Get the redis adapter based on the application configuration.
     *
     * @return Redis The Prometheus Redis adapter.
     */
    private function getRedisAdapter(): Redis
    {
        /** @var Repository $config */
        $config = $this->app['config'];

        $bridge = static function (array $illuminateConfig): array {
            return [
                'host'                   => $illuminateConfig['host'] ?? '127.0.0.1',
                'port'                   => $illuminateConfig['port'] ?? 6379,
                'timeout'                => 0.1,
                'read_timeout'           => 10,
                'persistent_connections' => false,
                'password'               => $illuminateConfig['password'] ?? null,
            ];
        };

        if ($config->has('prometheus.redis.connection')) {
            $connection = $config->get('prometheus.redis.connection');

            if (!$config->has("database.redis.$connection")) {
                throw new RuntimeException(sprintf(
                    'Redis connection "%s" does not exists in %s',
                    $connection,
                    $this->app->basePath('config/database.php'),
                ));
            }

            $options = $bridge($config->get("database.redis.$connection"));
        } else {
            $options = $bridge($config->get('prometheus.redis'));
        }

        if (array_key_exists('prefix', $options) && is_string($options['prefix'])) {
            Redis::setPrefix($options['prefix']);
        }

        return new Redis($options);
    }
}
