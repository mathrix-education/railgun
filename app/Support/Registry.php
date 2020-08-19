<?php

declare(strict_types=1);

namespace Railgun\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Validator;
use function array_key_exists;
use function is_array;
use function sprintf;

abstract class Registry
{
    protected bool   $cache = true;
    protected string $defaultPath;
    protected string $configPath;

    protected Container $container;
    /** @var mixed[] $instances */
    protected array $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function config(): Repository
    {
        return $this->container->make('config');
    }

    /**
     * Validation rules for every configuration.
     *
     * @return mixed[]
     */
    abstract protected function rules(): array;

    /**
     * @param string $name The configuration name.
     *
     * @return mixed[]|mixed
     */
    protected function options(string $name)
    {
        $options = $this->config()->get(sprintf($this->configPath, $name));

        if (is_array($options)) {
            $validator = Validator::make($options, $this->rules());
            $validator->validate();
        }

        return $options;
    }

    /**
     * Make an instance using the options.
     *
     * @param string $name    The configuration name.
     * @param mixed  $options The configuration options.
     *
     * @return mixed
     */
    abstract protected function factory(string $name, $options);

    /**
     * Parse the configuration name (fallback to default if name is null).
     *
     * @param string|null $name The configuration name.
     *
     * @return string The resolved configuration name.
     */
    public function name(?string $name = null): string
    {
        return $name ?? $this->config()->get($this->defaultPath);
    }

    /**
     * Make the instance.
     *
     * @param string|null $name The configuration name.
     *
     * @return mixed
     */
    public function make(?string $name = null)
    {
        $name = $this->name($name);

        if ($this->cache && array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        $instance = $this->factory($name, $this->options($name));

        if ($this->cache) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Forward any call to to the default instance.
     *
     * @param string  $method The method name.
     * @param mixed[] $args   The method arguments.
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function __call(string $method, array $args)
    {
        return $this->make()->{$method}(...$args);
    }
}
