<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Database\Eloquent\Model;
use Railgun\Billing\Stripe\Models\StripeSellable as StripeSellableContract;
use Railgun\Models\Traits\SaveWithoutEvents;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use function array_merge;
use function count;
use function resolveMorph;

/**
 * @mixin StripeSellableContract
 */
trait StripeSellable
{
    use InteractsWithStripe;
    use SaveWithoutEvents;

    protected StripeClient $client;

    public static function bootStripeSellable(): void
    {
        static::created(static function (Model $model): void {
            /** @var StripeSellable $model */
            if ($model->isStripeDirty()) {
                $model->pushToStripe('create');
            }
        });

        static::updated(static function (Model $model): void {
            /** @var StripeSellable $model */
            if ($model->isStripeDirty()) {
                $model->pushToStripe('update');
            }
        });
    }

    public function isStripeDirty(): bool
    {
        return ($this->stripe_price_id === null && $this->price !== null)
            || ($this->stripe_price_id !== null && $this->isDirty(['name', 'price', 'available']));
    }

    public function getIsSellableAttribute(): bool
    {
        return $this->price !== null;
    }

    /**
     * @return mixed[] The product and price metadata.
     */
    protected function getStripeMetadata(): array
    {
        return [
            'product_type' => resolveMorph(static::class),
            'product_id'   => $this->getKey(),
        ];
    }

    /**
     * Get the associated Stripe product if it exists, return null otherwise.
     *
     * @return Product|null The Stripe product instance if it exists, null otherwise.
     *
     * @throws ApiErrorException
     */
    protected function getStripeProduct(): ?Product
    {
        if ($this->stripe_product_id) {
            /** @var Product[] $results */
            $results = $this->client->products->all([
                'ids'   => [$this->stripe_product_id],
                'limit' => 1,
            ])->data;

            return count($results) > 0 ? $results[0] : null;
        }

        // There is no stripe_product_id, try to resolve by name
        /** @var Product $product */
        foreach ($this->client->products->all(['limit' => 100])->autoPagingIterator() as $product) {
            if ($product->name === $this->name) {
                // exact name match
                $this->stripe_product_id = $product->id;
                $this->saveWithoutEvents();

                return $product;
            }
        }

        return null;
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
        if ($this->getStripeProduct()) {
            return $this->updateStripeProduct();
        }

        return $this->client->products->create([
            'name'     => $this->name,
            'active'   => $this->available ?? true,
            'metadata' => $this->getStripeMetadata(),
        ]);
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
        $product = $this->getStripeProduct();

        if ($product) {
            return $product;
        }

        return $this->createStripeProduct();
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
        if (!$this->getStripeProduct()) {
            return $this->createStripeProduct();
        }

        return $this->client->products->update($this->stripe_product_id, [
            'name'     => $this->name,
            'active'   => $this->available ?? true,
            'metadata' => $this->getStripeMetadata(),
        ]);
    }

    /**
     * Get the associated Stripe price if it exists, return null otherwise.
     *
     * @return Price|null The Stripe price instance if it exists, null otherwise.
     *
     * @throws ApiErrorException
     */
    protected function getStripePrice(): ?Price
    {
        if ($this->stripe_price_id === null) {
            return null;
        }

        $results = $this->client->prices->all(['product' => $this->stripe_product_id])->autoPagingIterator();

        /** @var Price $price */
        foreach ($results as $price) {
            if (isset($price->metadata['legacy'])) {
                // Ignore "legacy" prices
                continue;
            }

            return $price;
        }

        return null;
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
        /** @var Price[] $prices */
        $prices = $this->client->prices->all(['product' => $this->stripe_product_id])->data;

        if (count($prices) > 0) {
            // An existing price exists, seemingly update
            $this->stripe_price_id = $prices[0]->id;
            $this->saveWithoutEvents();

            return $this->updateStripePrice();
        }

        // No price was found, go ahead
        return $this->client->prices->create([
            'active'         => $this->available ?? true,
            'billing_scheme' => 'per_unit',
            'currency'       => 'eur',
            'metadata'       => $this->getStripeMetadata(),
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

        $price = $this->getStripePrice();

        if (!$price) {
            return $this->createStripePrice();
        }

        return $price;
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
        if ($this->stripe_price_id === null) {
            $this->createStripePrice();
        }

        $price = $this->getStripePrice();

        if (!$price) {
            $this->stripe_price_id = null;
            $this->saveWithoutEvents();
        }

        if ($this->isDirty('price')) {
            // If price was changed, we cannot update the chapter Stripe Price and have to create a new one.
            if ($price) {
                $this->client->prices->update($price->id, [
                    'active'   => false,
                    'metadata' => array_merge(
                        $this->getStripeMetadata(),
                        ['legacy' => 'true'],
                    ),
                ]);
            }

            return $this->createStripePrice();
        }

        if (!$price) {
            // If there is no price, just create
            return $this->createStripePrice();
        }

        return $this->client->prices->update($this->stripe_price_id, [
            'active'   => $this->available ?? true,
            'metadata' => $this->getStripeMetadata(),
            'nickname' => $this->name,
        ]);
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
        $product                 = $intent === 'create'
            ? $this->createStripeProduct()
            : $this->updateStripePrice();
        $this->stripe_product_id = $product->id;
        $this->saveWithoutEvents();

        $price                 = $intent === 'create'
            ? $this->createStripePrice()
            : $this->updateStripePrice();
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
