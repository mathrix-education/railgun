<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use function array_merge;

class RedisCheck extends Check
{
    public const NAME = 'redis';

    private RedisManager $manager;
    private Connection   $redis;

    /**
     * @param mixed[] $options The Redis check options.
     *
     * @throws BindingResolutionException
     */
    public function __construct(array $options = [])
    {
        $this->manager = Container::getInstance()->make('redis');

        parent::__construct(array_merge([
            'connection' => $this->manager->getName(),
        ], $options));

        $this->redis = $this->manager->connection($this->options['connection']);
    }

    /**
     * @return string The Redis check name, based on if the selected connection is the default one.
     */
    public function getName(): string
    {
        return 'redis' . (
            $this->options['connection'] === $this->manager->getName()
                ? ''
                : '_' . $this->manager->getName()
            );
    }

    /**
     * Execute a series of set, get and del commands.
     *
     * @return mixed[] The Redis check details.
     */
    public function execute(): array
    {
        return [
            'get' => $this->redis->command('set', ['hello', 'miam']),
            'set' => $this->redis->command('get', ['hello']),
            'del' => $this->redis->command('del', ['hello']),
        ];
    }
}
