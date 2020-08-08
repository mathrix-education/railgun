<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Chapter;
use Railgun\Models\User;
use Stripe\StripeClient;

class CheckoutProducts
{
    protected StripeClient $client;

    public function __construct(StripeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (path).
     *
     * @return mixed
     */
    public function __invoke($_, array $args, GraphQLContext $context)
    {
        /** @var User $user */
        $user = $context->user();

        $payload = [
            'cancel_url'            => 'https://mathrix.fr',
            'success_url'           => 'https://mathrix.fr/mode-machine',
            'mode'                  => 'payment',
            'payment_method_types'  => ['card'],
            'client_reference_id'   => $user->id,
            'customer_email'        => $user->email,
            'line_items'            => Chapter::query()
                ->whereNotNull('stripe_price_id')
                ->inRandomOrder()->limit(3)
                ->pluck('stripe_price_id')
                ->map(static fn($id) => [
                    'price'    => $id,
                    'quantity' => 1,
                ])
                ->toArray(),
            'allow_promotion_codes' => true,
        ];

        if ($user->stripe_id) {
            $payload['customer'] = $user->stripe_id;
        }

        return $this->client->checkout->sessions->create($payload);
    }
}
