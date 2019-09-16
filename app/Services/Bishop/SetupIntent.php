<?php

declare(strict_types=1);

namespace App\Services\Bishop;

use Carbon\Carbon;
use Stripe\SetupIntent as StripeSetupIntent;

/**
 * @property-read string $id
 * @property-read string $client_secret
 * @property-read Carbon $created_at
 * @property-read string $status
 */
class SetupIntent extends Bishop
{
    protected $dates = ['created_at'];

    public static function from($setupIntent)
    {
        if ($setupIntent instanceof StripeSetupIntent) {
            return new self(self::fromStripe($setupIntent));
        }

        return null;
    }

    public static function fromStripe(StripeSetupIntent $setupIntent)
    {
        return [
            'id' => $setupIntent->id,
            'client_secret' => $setupIntent->client_secret,
            'created_at' => $setupIntent->created,
            'status' => $setupIntent->status,
        ];
    }
}
