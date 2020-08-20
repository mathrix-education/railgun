<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Exceptions;

use Railgun\Exceptions\HttpException;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Throwable;
use function trans;

class ThreeDSecureException extends HttpException
{
    protected string $category = 'billing';
    /** @var PaymentIntent|SetupIntent $object */
    protected $object;

    /**
     * @param PaymentIntent|SetupIntent $object   The Stripe object which triggered the 3DSecure.
     * @param Throwable|null            $previous The underlying exception, if necessary.
     */
    public function __construct($object, ?Throwable $previous = null)
    {
        parent::__construct(trans('stripe.errors.card.3ds2'), $previous);

        $this->object = $object;
    }

    /**
     * @return mixed[] The exception extensions.
     */
    public function extensions(): array
    {
        return [
            'type'          => $this->object->object,
            'client_secret' => $this->object->client_secret,
        ];
    }
}
