<?php

declare(strict_types=1);

namespace Mathrix\Healthz;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Mathrix\Healthz\Checks\Check;
use function collect;
use function config;

/**
 * Holds the {@see Check} declarations.
 *
 * @mixin Collection
 */
class ChecksRegistry
{
    /** @var Collection|Check[] The checks collection. */
    private Collection $checks;

    public function __construct()
    {
        $this->load();
    }

    /**
     * @return $this
     */
    public function load(): self
    {
        $this->checks = collect(config('healthz.checks'))
            ->map(static function (array $data) {
                $class   = $data['class'];
                $options = Arr::except($data, ['class']) ?? [];

                Container::getInstance()->when($class)->needs('$options')->give($options);

                return Container::getInstance()->make($class);
            });

        return $this;
    }

    /**
     * Forward any "collection" calls to the uderlying collection.
     *
     * @param string  $name      The method name.
     * @param mixed[] $arguments The method arguments.
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->checks->{$name}(...$arguments);
    }
}
