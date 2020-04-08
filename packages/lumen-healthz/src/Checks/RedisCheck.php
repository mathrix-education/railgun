<?php
declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Illuminate\Container\Container;
use Illuminate\Redis\Connections\Connection;

class RedisCheck
{
    public const NAME = 'redis';

    private Connection $redis;

    public function __construct()
    {
        $this->redis = Container::getInstance()->make('redis.connection');
    }

    public function execute(): void
    {
        $this->redis->command('set', ['hello', 'miam']);
        $this->redis->command('get', ['hello']);
        $this->redis->command('del', ['hello']);
    }
}
