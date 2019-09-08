<?php

namespace App\Services\Bishop;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Stripe\Card as StripeCard;

/**
 * Class Card.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 *
 * @property-read string $id
 * @property-read string $type
 * @property-read string $customer
 * @property-read Carbon $expiration
 * @property-read string $last4
 */
class Card extends Bishop
{
    protected $dates = [
        "expiration"
    ];


    /**
     * @param StripeCard $card
     *
     * @return Card
     */
    public static function from($card)
    {
        return new self(self::fromStripe($card));
    }


    /**
     * @param StripeCard $card
     *
     * @return array
     */
    public static function fromStripe(StripeCard $card): array
    {
        return [
            "id" => $card->id,
            "type" => "card",
            "brand" => Str::slug($card->brand),
            "expiration" => Carbon::create(
                $card->exp_year, $card->exp_month, 1, 0, 0, 0
            ),
            "fingerprint" => $card->fingerprint,
            "last4" => $card->last4,
        ];
    }
}
