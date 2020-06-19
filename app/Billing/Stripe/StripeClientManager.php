<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Stripe\StripeClient;

class StripeClientManager
{
    private Container $app;
    /** @var Collection|StripeClient[] $clients */
    private Collection $clients;

    public function __construct(Container $app)
    {
        $this->app     = $app;
        $this->clients = new Collection();
    }

    public function client(?string $name = null): StripeClient
    {
        $name ??= $this->default();

        if (!$this->clients->has($name)) {
            $this->clients->offsetSet($name, $this->factory($this->config($name)));
        }

        return $this->clients->get($name);
    }

    protected function default(): string
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        return $config->get('stripe.default');
    }

    /**
     * Get teh StripeClient configuration.
     *
     * @param string $name The StripeClient configuration name.
     *
     * @return array<string, mixed>|string|null The StripeClient configuration.
     *
     * @throws BindingResolutionException
     */
    protected function config(string $name)
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        return $config->get("stripe.accounts.$name.client");
    }

    /**
     * @param array<string, mixed>|string $options The StripeClient options ({@see BaseStripeClient::__construct}).
     *
     * @return StripeClient The new StripeClient.
     */
    protected function factory($options): StripeClient
    {
        return new StripeClient($options);
    }
}
