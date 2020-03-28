<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use function app;

abstract class Job
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param Throwable|null $exception The exception thrown during the job execution.
     *
     * @throws Exception
     */
    public function fail($exception = null): void
    {
        if (!($exception instanceof Throwable)) {
            return;
        }

        /** @var ExceptionHandler $handler */
        $handler = app()->make(ExceptionHandler::class);
        $handler->report($exception);
    }
}
