<?php

declare(strict_types=1);

namespace Mathrix\Silicate;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use function array_key_exists;
use function array_search;

class BrickRegistry
{
    /** @var string[] $map The bricks maps [kind => class] */
    private array $map;

    /**
     * @param string $class The brick class.
     *
     * @throws ReflectionException
     */
    public function register(string $class): void
    {
        $reflection = new ReflectionClass($class);
        $kind       = $reflection->getConstant('KIND');

        if ($kind === false) {
            throw new RuntimeException("$class::KIND does not exists");
        }

        $this->map[$kind] = $class;
    }

    public function getClass(string $kind): ?string
    {
        return array_key_exists($kind, $this->map) ? $this->map['kind'] : null;
    }

    public function getKind(string $class): ?string
    {
        return array_search($class, $this->map, true) ?: null;
    }
}
