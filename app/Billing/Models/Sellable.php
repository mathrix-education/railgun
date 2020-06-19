<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Railgun\Models\Traits\SaveWithoutEvents;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use function array_combine;
use function array_merge;
use function class_basename;
use function explode;
use function stripe;

/**
 * @property-read string|int $id
 * @property      string     $name
 * @property      int        $price
 * @property      string     $stripe_product_id
 * @property      string     $stripe_price_id
 * @mixin Model
 * @mixin SaveWithoutEvents
 */
trait Sellable
{
    public static function bootSellable(): void
    {
        static::saved(static function (Model $model): void {
            /** @var Sellable $model */
            if ($model->shouldPushToStripe()) {
                $model->pushToStripe();
            }
        });
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
     * @return mixed[] The {@see Sellable} payload for the Stripe API.
     */
    protected function getProductPayload(): array
    {
        return [
            'id'       => $this->stripe_product_id,
            'name'     => $this->name,
            'active'   => $this->available ?? true,
            'metadata' => $this->getProductMetadata(),
        ];
    }

    /**
     * @return mixed[] The {@see Price} payload for the Stripe API.
     */
    protected function getPricePayload(): array
    {
        return [
            'active'         => $this->available ?? true,
            'billing_scheme' => 'per_unit',
            'currency'       => 'eur',
            'metadata'       => $this->getProductMetadata(),
            'nickname'       => $this->name,
            'unit_amount'    => $this->price,
        ];
    }

    public function hasLinkedStripeProduct(): bool
    {
        return $this->stripe_price_id !== null;
    }

    public function shouldPushToStripe(): bool
    {
        return (!$this->hasLinkedStripeProduct() && $this->price !== null)
            || ($this->hasLinkedStripeProduct() && $this->wasChanged(['name', 'price', 'available']));
    }

    /**
     * Push the model to Stripe.
     *
     * @throws ApiErrorException
     */
    public function pushToStripe(): void
    {
        if ($this->hasLinkedStripeProduct()) {
            stripe()->products->update(
                $this->stripe_product_id,
                Arr::except($this->getProductPayload(), ['id', 'billing_scheme', 'currency', 'unit_amount']),
            );

            stripe()->prices->update(
                $this->stripe_price_id,
                Arr::except($this->getPricePayload(), ['billing_scheme', 'currency', 'unit_amount']),
            );
        } else {
            try {
                $product = stripe()->products->create($this->getProductPayload());
            } catch (InvalidRequestException $exception) {
                if ($exception->getError()->code !== ErrorObject::CODE_RESOURCE_ALREADY_EXISTS) {
                    throw $exception; // rethrow
                }

                $product = stripe()->products->retrieve($this->stripe_product_id);
            }

            $prices = stripe()->prices->all(['product' => $product->id]);

            if ($prices->count() > 0) {
                $price = $prices->data[0];
            } else {
                $price = stripe()->prices->create(array_merge($this->getPricePayload(), ['product' => $product->id]));
            }

            $this->stripe_price_id = $price->id;

            $this->saveWithoutEvents();
        }
    }
}
