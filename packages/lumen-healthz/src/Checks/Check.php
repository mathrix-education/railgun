<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Illuminate\Support\Str;
use function array_merge;
use function class_basename;
use function str_replace;

abstract class Check
{
    /** @var mixed[] $options The check options. */
    protected array $options = ['details' => true];

    /**
     * @param mixed[] $options The check options.
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @return string The check name.
     */
    public function getName(): string
    {
        $name = str_replace('Check', '', class_basename($this));

        return Str::snake($name);
    }

    /**
     * @return bool If the check details should be displayed.
     */
    public function exposeDetails(): bool
    {
        return $this->options['details'] === true;
    }

    /**
     * @return mixed[] The check details.
     */
    abstract public function execute(): array;
}
