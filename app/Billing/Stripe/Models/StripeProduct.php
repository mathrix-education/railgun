<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Railgun\Billing\Stripe\InteractsWithStripe;
use Railgun\Models\Traits\SaveWithoutEvents;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use function array_combine;
use function class_basename;
use function explode;

/**
 * @property-read string|int $id
 * @property      string     $name
 * @property      int        $price
 * @property-read string     $is_sellable
 * @property-read string     $stripe_product_id
 * @property      string     $stripe_price_id
 * @mixin Model
 * @mixin SaveWithoutEvents
 */
trait StripeProduct
{
    use InteractsWithStripe;
    use SaveWithoutEvents;

    protected StripeClient $client;

    public static function bootStripeProduct(): void
    {
        static::saved(static function (Model $model): void {
            /** @var StripeProduct $model */
            if ($model->isStripeDirty()) {
                $model->pushToStripe();
            }
        });
    }

    public function isSellable(): bool
    {
        return $this->price !== null;
    }

    public function isStripeDirty(): bool
    {
        return ($this->stripe_price_id === null && $this->price !== null)
            || ($this->stripe_price_id !== null && $this->wasChanged(['name', 'price', 'available']));
    }

    /**
     * @return string The product id.
     */
    public function getStripeProductIdAttribute(): string
    {
        return Str::of(class_basename($this))->slug()->lower() . '_' . $this->id;
    }

    /**
     * @return mixed[] The product and price metadata.
     */
    protected function getProductMetadata(): array
    {
        return array_combine(['product_type', 'product_id'], explode('_', $this->stripe_product_id));
    }

    /**
     * Create or retrieve the Stripe product.
     *
     * @return Product The Stripe product.
     *
     * @throws ApiErrorException
     */
    public function createStripeProduct(): Product
    {
        try {
            return $this->client->products->create([
                'id'       => $this->stripe_product_id,
                'name'     => $this->name,
                'active'   => $this->available ?? true,
                'metadata' => $this->getProductMetadata(),
            ]);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_ALREADY_EXISTS) {
                return $this->retrieveStripeProduct();
            }

            throw $exception;
        }
    }

    /**
     * Retrieve or create the Stripe product.
     *
     * @return Product The Stripe product.
     *
     * @throws ApiErrorException
     */
    public function retrieveStripeProduct(): Product
    {
        try {
            return $this->client->products->retrieve($this->stripe_product_id);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                return $this->createStripeProduct();
            }

            throw $exception;
        }
    }

    /**
     * Update or create the Stripe product.
     *
     * @return Product The Stripe product.
     *
     * @throws ApiErrorException
     */
    public function updateStripeProduct(): Product
    {
        try {
            return $this->client->products->update($this->stripe_product_id, [
                'name'     => $this->name,
                'active'   => $this->available ?? true,
                'metadata' => $this->getProductMetadata(),
            ]);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                return $this->createStripeProduct();
            }

            throw $exception;
        }
    }

    /**
     * Create or retrieve the Stripe Price.
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    public function createStripePrice(): Price
    {
        if ($this->stripe_price_id !== null) {
            $this->updateStripePrice();
        }

        // price_id is null but is still possible that a price exists for that product
        $prices = $this->client->prices->all(['product' => $this->stripe_product_id]);

        if ($prices->count() > 0) {
            // An existing price exists, seemingly update
            $this->stripe_price_id = $prices->data[0]->id;
            $this->saveWithoutEvents();

            return $this->updateStripePrice();
        }

        // No price was found, go ahead
        return $this->client->prices->create([
            'active'         => $this->available ?? true,
            'billing_scheme' => 'per_unit',
            'currency'       => 'eur',
            'metadata'       => $this->getProductMetadata(),
            'nickname'       => $this->name,
            'unit_amount'    => $this->price,
            'product'        => $this->stripe_product_id,
        ]);
    }

    /**
     * Retrieve or create the Stripe Price.
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    public function retrieveStripePrice(): Price
    {
        if ($this->stripe_price_id === null) {
            return $this->createStripePrice();
        }

        try {
            return $this->client->prices->retrieve($this->stripe_price_id);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                return $this->createStripePrice();
            }

            throw $exception;
        }
    }

    /**
     * Update or create the Stripe Price.
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    public function updateStripePrice(): Price
    {
        if ($this->stripe_price_id === null || $this->isDirty('price')) {
            // If price was changed, we cannot update the chapter Stripe Price and have to create a new one.
            $this->createStripePrice();
        }

        try {
            return $this->client->prices->update($this->stripe_price_id, [
                'active'   => $this->available ?? true,
                'metadata' => $this->getProductMetadata(),
                'nickname' => $this->name,
            ]);
        } catch (ApiErrorException $exception) {
            if ($exception->getStripeCode() === ErrorObject::CODE_RESOURCE_MISSING) {
                return $this->createStripePrice();
            }

            throw $exception;
        }
    }

    /**
     * Push the model to Stripe as a Product.
     *
     * @param string $intent The intent, which is the default behavior (create or update).
     *
     * @throws ApiErrorException
     */
    public function pushToStripe(string $intent = 'update'): void
    {
        if ($intent === 'create') {
            $this->createStripeProduct();
            $price = $this->createStripePrice();
        } else {
            $this->updateStripeProduct();
            $price = $this->updateStripePrice();
        }

        // Save the newly created price
        $this->stripe_price_id = $price->id;
        $this->saveWithoutEvents();
    }

    /**
     * Pull the product and associated price data from Stripe.
     *
     * @throws ApiErrorException
     */
    public function pullFromStripe(): void
    {
        $product = $this->retrieveStripeProduct();
        $price   = $this->retrieveStripePrice();

        $this->name            = $product->name;
        $this->available       = $product->active;
        $this->price           = $price->unit_amount;
        $this->stripe_price_id = $price->id;
        $this->saveWithoutEvents();
    }
}
