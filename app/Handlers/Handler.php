<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Exception;
use GraphQL\Error\Error as GraphQLError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Railgun\Billing\Exceptions\Card;
use Railgun\Exceptions\Validation;
use Railgun\Models\User;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Stripe\Exception\CardException;
use Throwable;
use function app;

/**
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var string[]
     */
    protected $dontReport = [];

    /**
     * Transform an exception before reporting.
     *
     * @param Throwable $original
     *
     * @return Throwable
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
                    $converted
                );
            }
        }

        if ($original instanceof ValidationException) {
            // Convert validation exceptions
            return Validation::from($original);
        }

        if ($original instanceof CardException) {
            // Convert Stripe card exceptions
            return Card::from($original);
        }

        return $original;
    }

    /**
     * @param Throwable $exception The exception to report.
     *
     * @throws Exception
     */
    public function report(Throwable $exception): void
    {
        $exception = $this->transform($exception);

        $this->reportToSentry($exception);

        parent::report($exception);
    }

    /**
     * Report the exception to sentry if necessary.
     *
     * @param Throwable $exception The exception to report.
     */
    public function reportToSentry(Throwable $exception): void
    {
        if (!$this->shouldReport($exception)
            || !app()->environment(['master', 'dev', 'v7'])
            || !app()->bound('sentry')
        ) {
            return;
        }

        /** @var Hub $sentry */
        $sentry = app('sentry');

        /** @var User|null $user The logged user */
        $user = Auth::user();

        if ($user instanceof User) {
            $sentry->configureScope(static function (Scope $scope) use ($user) {
                $scope->setUser($user->toArray());
            });
        }

        $sentry->captureException($exception);
    }
}
