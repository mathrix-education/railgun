<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Railgun\Middleware\CorrelationMiddleware;
use Railgun\Models\User;
use Sentry\Severity;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;
use function app;

/**
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 *
 * @codeCoverageIgnore
 */
class Handler extends ExceptionHandler
{
    private ExceptionPipeline $pipeline;

    public function __construct(ExceptionPipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @param Request|mixed $request   The Illuminate HTTP request.
     * @param Throwable     $exception The exception to render.
     *
     * @return JsonResponse|Response|SymfonyResponse The response object to send.
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception): SymfonyResponse
    {
        return parent::render($request, $this->pipeline->transform($exception));
    }

    /**
     * @param Throwable $exception The exception to report.
     *
     * @throws Exception
     */
    public function report(Throwable $exception): void
    {
        $transformed = $this->pipeline->transform($exception);

        if ($this->shouldntReport($exception) || $this->shouldntReport($transformed)) {
            return;
        }

        $this->reportToSentry($transformed);

        parent::report($transformed);
    }

    /**
     * Report the exception to sentry if necessary.
     *
     * @param Throwable $exception The exception to report.
     */
    public function reportToSentry(Throwable $exception): void
    {
        if ($exception instanceof HttpException && $exception->getLevel() === LogLevel::DEBUG) {
            // Ignore DEBUG exceptions
            return;
        }

        if (!app()->environment(['production', 'edge']) || !app()->bound('sentry')) {
            return;
        }

        /** @var Hub $sentry */
        $sentry = app('sentry');

        $sentry->configureScope(function (Scope $scope) use ($exception): void {
            /** @var User|null $user The logged user */
            $user = Auth::user();

            if ($user instanceof User) {
                $scope->setUser($user->toArray());
            }

            // Set the exception level if applicable
            if ($exception instanceof HttpException) {
                $scope->setLevel($this->getSentrySeverity($exception->getLevel()));
            }

            // Set the correlation id
            if (app()->bound(CorrelationMiddleware::ABSTRACT)) {
                $scope->setTag('correlation_id', app()->make(CorrelationMiddleware::ABSTRACT));
            }
        });

        $sentry->captureException($exception);
    }

    /**
     * Convert a PSR3 {@see LogLevel} to a Sentry {@see Severity}.
     *
     * @param string $psr3LogLevel The PSR3 {@see LogLevel}
     *
     * @return Severity|null The Sentry {@see Severity}
     */
    private function getSentrySeverity(string $psr3LogLevel): ?Severity
    {
        switch ($psr3LogLevel) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                return Severity::fatal();
            case LogLevel::ERROR:
                return Severity::error();
            case LogLevel::WARNING:
                return Severity::warning();
            case LogLevel::NOTICE:
            case LogLevel::INFO:
                return Severity::info();
            case LogLevel::DEBUG:
                return Severity::debug();
            default:
                return null;
        }
    }
}
