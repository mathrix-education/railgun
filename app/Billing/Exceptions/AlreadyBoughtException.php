<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Illuminate\Support\Facades\Auth;
use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to buy a product he already owns.
 */
class AlreadyBoughtException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    /**
     * @param string[]       $productNames The products name.
     * @param int|null       $userId       The user ID.
     * @param Throwable|null $previous     The previous exception, if any.
     */
    public function __construct(
        array $productNames,
        ?int $userId = null,
        ?Throwable $previous = null
    )
    {
        $names = implode(', ', $productNames);

        if ($userId === Auth::id()) {
            $message = trans('billing.errors.already_bought_you', ['name' => $names]);
        } else {
            $message = trans('billing.errors.already_bought', ['name' => $names, 'id' => $userId]);
        }

        parent::__construct($message, $previous);
    }
}
