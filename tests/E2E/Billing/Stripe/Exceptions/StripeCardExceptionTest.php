<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Exceptions;

use Railgun\Billing\Stripe\Exceptions\StripeCardException;
use Stripe\Charge;
use Stripe\ErrorObject;
use Stripe\Exception\CardException;
use Tests\ApplicationTestCase;
use function collect;
use function is_array;
use function trans;

/**
 * @coversDefaultClass \Railgun\Billing\Stripe\Exceptions\StripeCardException
 */
class StripeCardExceptionTest extends ApplicationTestCase
{
    /**
     * @return CardException[] The CardExceptions dataset.
     */
    public function getTestFromDataset(): array
    {
        $map = [
            ErrorObject::CODE_EXPIRED_CARD         => trans('stripe.errors.card.expired'),
            ErrorObject::CODE_INCORRECT_CVC        => trans('stripe.errors.card.incorrect_cvc'),
            ErrorObject::CODE_INVALID_CVC          => trans('stripe.errors.card.incorrect_cvc'),
            ErrorObject::CODE_INVALID_EXPIRY_MONTH => trans('stripe.errors.card.incorrect_exp'),
            ErrorObject::CODE_INVALID_EXPIRY_YEAR  => trans('stripe.errors.card.incorrect_exp'),
            ErrorObject::CODE_CARD_DECLINED        => [
                'declineCode' => Charge::DECLINED_INSUFFICIENT_FUNDS,
                'message'     => trans('stripe.errors.card.insufficient_funds'),
            ],
            'unknown'                              => trans('stripe.errors.card.generic'),
        ];

        return collect($map)->mapWithKeys(static function ($data, string $stripeCode) {
            if (is_array($data)) {
                $declineCode     = $data['declineCode'];
                $expectedMessage = $data['message'];
            } else {
                $declineCode     = null;
                $expectedMessage = $data;
            }

            return [
                $stripeCode => [
                    'original'        => CardException::factory(
                        'Test message',
                        400,
                        null,
                        null,
                        null,
                        $stripeCode,
                        $declineCode,
                    ),
                    'expectedMessage' => $expectedMessage,
                ],
            ];
        })->toArray();
    }

    /**
     * @dataProvider getTestFromDataset
     *
     * @param CardException $original        The original Stripe CardException
     * @param string        $expectedMessage The expected transformed message.
     */
    public function testFrom(CardException $original, string $expectedMessage): void
    {
        $exception = StripeCardException::from($original);
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    /**
     * @dataProvider getTestFromDataset
     *
     * @param CardException $original The original Stripe CardException
     */
    public function testExtensions(CardException $original): void
    {
        $exception = StripeCardException::from($original);

        $this->assertEquals(
            ['stripeCode' => $original->getStripeCode()],
            $exception->extensions(),
        );
    }

    public function testExtensionsUnknownException(): void
    {
        $exception = new StripeCardException();
        $this->assertEquals([], $exception->extensions());
    }
}
