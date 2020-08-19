<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Database\Eloquent\Model;
use Railgun\Billing\Stripe\Contracts\StripeSellable as StripeSellableContract;
use Railgun\Models\Traits\Sellable;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use function count;
use function resolveMorph;

/**
 * @mixin StripeSellableContract
 */
trait StripeSellable
{
    use InteractsWithStripe;
    use Sellable;

    public static function bootStripeSellable(): void
    {
        static::created(static function (Model $model): void {
            /** @var StripeSellable $model */
            if ($model->isStripeDirty()) {
                $model->ensureStripeProduct('create');
                $model->ensureStripePrice('create');
            }
        });

        static::updated(static function (Model $model): void {
            /** @var StripeSellable $model */
            if ($model->isStripeDirty()) {
                $model->ensureStripeProduct('update');
                $model->ensureStripePrice('update');
            }
        });
    }

    protected function isStripeDirty(): bool
    {
        return ($this->stripe_price_id === null && $this->price !== null)
            || ($this->stripe_price_id !== null && $this->isDirty(['name', 'price', 'available', static::UPDATED_AT]));
    }

    protected function isStripeProduct(Product $product): bool
    {
        return $this->name === $product->name
            && resolveMorph(static::class) === $product->metadata['product_type'];
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
    public function getStripeProductAttribute(): ?Product
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
            if ($this->isStripeProduct($product)) {
                // exact name match
                $this->stripe_product_id = $product->id;
                $this->saveWithoutEvents();

                return $product;
            }
        }

        return null;
    }

    /**
     * Push the model to Stripe as a Product.
     *
     * @param string $intent The intent, which is the default behavior (create or update).
     *
     * @return Product The Stripe product instance.
     *
     * @throws ApiErrorException
     */
    protected function ensureStripeProduct(string $intent = 'update'): Product
    {
        $product = $intent === 'create'
            ? $this->createStripeProduct()
            : $this->updateStripeProduct();

        $this->stripe_product_id = $product->id;
        $this->saveWithoutEvents();

        return $product;
    }

    /**
     * Create or retrieve the Stripe product.
     *
     * @return Product The Stripe product.
     *
     * @throws ApiErrorException
     */
    protected function createStripeProduct(): Product
    {
        if ($this->stripe_product) {
            return $this->updateStripeProduct();
        }

        return $this->client->products->create([
            'name'     => $this->name,
            'active'   => $this->available ?? true,
            'metadata' => $this->getStripeMetadata(),
        ]);
    }

    /**
     * Update or create the Stripe product.
     *
     * @return Product The Stripe product instance.
     *
     * @throws ApiErrorException
     */
    protected function updateStripeProduct(): Product
    {
        if (!$this->stripe_product) {
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
    public function getStripePriceAttribute(): ?Price
    {
        if ($this->stripe_price_id === null) {
            return null;
        }

        $results = $this->client->prices->all(['product' => $this->stripe_product_id])->autoPagingIterator();

        /** @var Price $price */
        foreach ($results as $price) {
            if (!$price->active) {
                // Ignore inactive prices, but should never be used as the first price of the collection is always
                // the latest one.
                continue; // @codeCoverageIgnore
            }

            return $price;
        }

        return null;
    }

    /**
     * Ensure that the product has an associated Stripe Price.
     *
     * @param string $intent The intent, which is the default behavior (create or update).
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    protected function ensureStripePrice(string $intent = 'update'): Price
    {
        $price = $intent === 'create'
            ? $this->createStripePrice()
            : $this->updateStripePrice();

        $this->stripe_price_id = $price->id;
        $this->saveWithoutEvents();

        return $price;
    }

    /**
     * Create or retrieve the Stripe Price.
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    protected function createStripePrice(): Price
    {
        if ($this->stripe_price_id !== null) {
            $this->updateStripePrice();
        }

        // price_id is null but is still possible that a price exists for that product
        $found = null;

        /** @var Price $price */
        foreach ($this->client->prices->all(['product' => $this->stripe_product_id])->autoPagingIterator() as $price) {
            if (!$price->active) {
                // Ignore inactive prices, but should never be used as the first price of the collection is always
                // the latest one.
                continue; // @codeCoverageIgnore
            }

            $found = $price;
            break;
        }

        if ($found) {
            // An existing price exists, seemingly update
            $this->stripe_price_id = $found->id;
            $this->saveWithoutEvents();

            return $this->updateStripePrice();
        }

        // No price was found, go ahead
        return $this->client->prices->create([
            'billing_scheme' => 'per_unit',
            'currency'       => 'eur',
            'metadata'       => $this->getStripeMetadata(),
            'nickname'       => $this->name,
            'unit_amount'    => $this->price,
            'product'        => $this->stripe_product_id,
        ]);
    }

    /**
     * Update or create the Stripe Price.
     *
     * @return Price The Stripe Price instance.
     *
     * @throws ApiErrorException
     */
    protected function updateStripePrice(): Price
    {
        if ($this->stripe_price_id === null) {
            $this->createStripePrice();
        }

        $price = $this->stripe_price;

        if (!$price) {
            $this->stripe_price_id = null;
            $this->saveWithoutEvents();
        }

        if ($this->wasChanged('price')) {
            // If price was changed, we cannot update the chapter Stripe Price and have to create a new one.
            if ($price) {
                $this->client->prices->update($price->id, ['active' => false]);

                $this->stripe_price_id = null;
                $this->saveWithoutEvents();
            }

            return $this->createStripePrice();
        }

        if (!$price) {
            // If there is no price, just create
            return $this->createStripePrice();
        }

        return $this->client->prices->update($this->stripe_price_id, [
            'metadata' => $this->getStripeMetadata(),
            'nickname' => $this->name,
        ]);
    }
}
