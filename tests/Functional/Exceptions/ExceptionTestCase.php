<?php

declare(strict_types=1);

namespace Tests\Functional\Exceptions;

use Closure;
use Railgun\Handlers\ExceptionPipeline;
use Tests\ApplicationTestCase;
use Throwable;

class ExceptionTestCase extends ApplicationTestCase
{
    /**
     * @param Closure $callback The callback.which should throw the exception.
     *
     * @throws Throwable
     */
    public function transform(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            /** @var ExceptionPipeline $pipeline */
            $pipeline = $this->app->make(ExceptionPipeline::class);
            throw $pipeline->transform($exception);
        }
    }
}
