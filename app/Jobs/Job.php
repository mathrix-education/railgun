<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use function app;

/**
 * Base class for jobs.
 */
abstract class Job
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Handle job failure.
     *
     * @link https://laravel.com/docs/7.x/queues#dealing-with-failed-jobs
     *
     * @param Throwable $exception The exception thrown during the job execution.
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    public function failed(Throwable $exception): void
    {
        /** @var ExceptionHandler $handler */
        $handler = app()->make(ExceptionHandler::class);
        $handler->report($exception);
    }
}
