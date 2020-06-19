<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Railgun\Billing\Products\Premium;
use Railgun\Models\Chapter;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Billing\Models\Sellable;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use function class_basename;
use function collect;
use function count;
use function forward_static_call_array;

class StripeInit extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected            $signature = 'stripe:init';
    private StripeClient $stripe;

    /**
     * @param StripeClient $stripe The injected Stripe Client.
     *
     * @throws ApiErrorException
     */
    public function handle(StripeClient $stripe): void
    {
        $this->stripe = $stripe;
        $this->premium();
        $this->products(Pack::class);
        $this->products(Training::class);
        $this->products(Chapter::class);
    }

    /**
     * Synchronize the premium product with Stripe.
     *
     * @throws ApiErrorException
     */
    public function premium(): void
    {
        $this->line('Synchronizing <info>premium</info> product');

        if ($this->getProduct(Premium::PRODUCT['id']) === null) {
            $this->stripe->products->create(Premium::PRODUCT);
        }

        $progress = $this->getOutput()->createProgressBar(count(Premium::PRICES));
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('');

        collect(Premium::PRICES)->each(function (array $payload, string $lookupKey) use ($progress): void {
            $progress->setMessage("Pushing <info>{$lookupKey}</info> price");
            $price = $this->getPrice($lookupKey, false);

            if ($price === null) {
                $this->stripe->prices->create($payload);
            } else {
                $this->stripe->prices->update(
                    $price->id,
                    Arr::except($payload, ['product', 'recurring', 'billing_scheme', 'currency', 'unit_amount']),
                );
            }

            $progress->advance();
        });

        $progress->finish();
        unset($progress);
        $this->getOutput()->newLine(2);
    }

    /**
     * Synchronize products (packs and trainings) with Stripe.
     *
     * @param string $model The model class.
     */
    public function products(string $model): void
    {
        $plural = Str::of(class_basename($model))->lower()->plural();
        $this->line("Synchronizing <info>$plural</info>");

        if ($model === Chapter::class) {
            $models = Chapter::query()->whereNotNull('price');
        } else {
            /** @var Collection|Sellable[] $models */
            $models = forward_static_call_array([$model, 'all'], []);
        }

        $progress = $this->getOutput()->createProgressBar($models->count());
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('');

        $progress->start();
        $models->each(static function ($model) use ($progress): void {
            /** @var Sellable $model */
            $progress->setMessage(
                "Pushing <info>{$model->name}</info> (product <info>{$model->stripe_product_id}</info>)",
            );
            $model->pushToStripe();
            $progress->advance();
        });
        $progress->finish();
        unset($progress);
        $this->getOutput()->newLine(2);
    }

    /**
     * Check if a product exists on Stripe.
     *
     * @param string $id  The product id.
     * @param bool   $log Log the result.
     *
     * @return Product|null The Stripe product if it exists, null otherwise.
     *
     * @throws ApiErrorException
     */
    private function getProduct(string $id, bool $log = true): ?Product
    {
        $res    = $this->stripe->products->all(['ids' => [$id]]);
        $exists = $res->count() > 0;
        /** @var Product $product */
        $product = $exists ? $res->data[0] : null;

        if ($log) {
            $this->line("Product $id <info>" . ($exists ? 'already exists' : 'does not exist') . '</info>');
        }

        return $product;
    }

    /**
     * Check if a price exists on Stripe.
     *
     * @param string $lk  The price id.
     * @param bool   $log Log the result.
     *
     * @return Product|null The Stripe price if it exists, null otherwise.
     *
     * @throws ApiErrorException
     */
    private function getPrice(string $lk, bool $log = true): ?Price
    {
        $res    = $this->stripe->prices->all(['lookup_keys' => [$lk]]);
        $exists = $res->count() > 0;
        /** @var Price $price */
        $price = $exists ? $res->data[0] : null;

        if ($log) {
            $this->line("Price $lk <info>" . ($exists ? 'already exists' : 'does not exist') . '</info>');
        }

        return $price;
    }
}
