<?php

declare(strict_types=1);

namespace Railgun\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use function array_key_exists;
use function strpos;
use function time;
use function with;

/**
 * Parse the output of "ppm status" command.
 *
 * @property-read string $status
 * @property-read int[]  $workers
 * @property-read int    $handled_requests
 * @property-read int[]  $handled_requests_per_worker
 */
class PHPPMStatus implements Arrayable
{
    /** @var int $ttl How long the status can be considered as "fresh" */
    private int $ttl = 1;
    /** @var int|null The last status timestamp. */
    private ?int $time = null;
    /** @var mixed[] $data The output of the status command. */
    private array $data;

    /**
     * Reload the status.
     */
    public function reload(): void
    {
        if (time() - $this->time <= $this->ttl) {
            return;
        }

        $process = new Process(['./vendor/bin/ppm', 'status']);
        $buffer  = '';

        $process->run(static function (string $type, string $data) use (&$buffer): void {
            if ($type === Process::OUT) {
                $buffer .= $data;
            }
        });

        $this->data = with(
            Str::of($buffer)
                ->substr(strpos($buffer, 'status'))
                ->trim()
                ->replace("\t", '  '), // replace tab indentation by two spaces
            static fn(Stringable $string) => Yaml::parse((string)$string),
        );
        $this->time = time();
    }

    /**
     * @param string $name The property name.
     *
     * @return bool If the property exists in the status.
     */
    public function __isset(string $name): bool
    {
        $this->reload();

        return array_key_exists($name, $this->data);
    }

    /**
     * @param string $name The property name.
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        $this->reload();

        if (!isset($this->{$name})) {
            return null;
        }

        return $this->data[$name];
    }

    /**
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     */
    public function __set(string $name, $value): void
    {
        // Setters are implement in reload method.
    }

    /**
     * Get the instance as an array.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        $this->reload();

        return $this->data;
    }
}
