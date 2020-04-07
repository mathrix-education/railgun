<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Exception;
use GraphQL\Error\Error as GraphQLError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use League\Flysystem\FileExistsException as LeagueFileExistsException;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Psr\Log\LogLevel;
use Railgun\Billing\Exceptions\CardException;
use Railgun\Billing\Exceptions\InvalidEventSignatureException;
use Railgun\Exceptions\FileConflictException;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Exceptions\HttpException;
use Railgun\Exceptions\MethodNotAllowedException;
use Railgun\Exceptions\ValidationException;
use Railgun\Middleware\CorrelationMiddleware;
use Railgun\Models\User;
use Sentry\Severity;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Stripe\Exception\CardException as StripeCardException;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use function app;

/**
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 */
class Handler extends ExceptionHandler
{
    /**
     * Transform an exception before reporting.
     *
     * @param Throwable $original The original exception.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original): Throwable
    {
        if ($original instanceof GraphQLError && $original->getPrevious() instanceof Throwable) {
            // Handle GraphQL embedded exceptions
            $embedded  = $original->getPrevious();
            $converted = $this->transform($embedded);

            if ($converted !== null && $embedded !== $converted) {
                // Exception need to be swapped
                return new GraphQLError(
                    $converted->getMessage(),
                    $original->nodes,
                    $original->getSource(),
                    $original->getPositions(),
                    $original->getPath(),
                    $converted,
                );
            }
        }

        if ($original instanceof MethodNotAllowedHttpException) {
            return MethodNotAllowedException::from($original);
        }

        if ($original instanceof IlluminateValidationException) {
            // Convert validation exceptions
            return ValidationException::from($original);
        }

        if ($original instanceof StripeCardException) {
            // Convert Stripe card exceptions
            return CardException::from($original);
        }

        if ($original instanceof LeagueFileNotFoundException) {
            // Convert League exception
            return new FileNotFoundException($original->getPath(), $original);
        }

        if ($original instanceof LeagueFileExistsException) {
            // Convert League exception
            return new FileConflictException($original->getPath(), $original);
        }

        if ($original instanceof SignatureVerificationException) {
            // Convert Stripe signature exception
            return new InvalidEventSignatureException($original);
        }

        return $original;
    }

    /**
     * @param Request   $request   The Illuminate HTTP request.
     * @param Throwable $exception The exception to render.
     *
     * @return JsonResponse|Response|SymfonyResponse The response object to send.
     *
     * @throws Throwable
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $this->transform($exception));
    }

    /**
     * @param Throwable $exception The exception to report.
     *
     * @throws Exception
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $exception = $this->transform($exception);

        $this->reportToSentry($exception);

        parent::report($exception);
    }

    /**
     * Report the exception to sentry if necessary.
     *
     * @param Throwable $exception The exception to report.
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
