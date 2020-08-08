<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Railgun\Billing\Stripe\Contracts\StripeProduct;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Chapter;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use function class_basename;
use function collect;
use function count;
use function forward_static_call_array;

class SyncProductsCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected            $signature = 'stripe:sync:products {--intent=create} {--client=}';
    private StripeClient $stripe;

    /**
     * @param StripeClientRegistry $registry The injected Stripe Client.
     *
     * @throws ApiErrorException
     */
    public function handle(StripeClientRegistry $registry): void
    {
        $this->stripe = $registry->make($this->option('client'));

        // TODO: remove the Chapter auto-pricing
        Chapter::query()
            ->whereHas('theme.grade', static fn($q) => $q->where('grades.slug', '=', 'postbac'))
            ->update(['price' => 4000]);

        $this->syncPremium();
        $this->syncProducts(Pack::class);
        $this->syncProducts(Training::class);
        $this->syncProducts(Chapter::class);
    }

    /**
     * Synchronize the premium product with Stripe.
     *
     * @throws ApiErrorException
     */
    public function syncPremium(): void
    {
        $this->line('Synchronizing <info>premium</info> product');

        if ($this->getProduct(ModeMachine::PRODUCT['id']) === null) {
            $this->stripe->products->create(ModeMachine::PRODUCT);
        }

        $progress = $this->getOutput()->createProgressBar(count(ModeMachine::PRICES));
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('');

        collect(ModeMachine::PRICES)->each(function (array $payload, string $lookupKey) use ($progress): void {
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
    public function syncProducts(string $model): void
    {
        $plural = Str::of(class_basename($model))->lower()->plural();
        $this->line("Synchronizing <info>$plural</info>");

        if ($model === Chapter::class) {
            $models = Chapter::query()->whereNotNull('price');
        } else {
            /** @var Collection|StripeProduct[] $models */
            $models = forward_static_call_array([$model, 'all'], []);
        }

        $progress = $this->getOutput()->createProgressBar($models->count());
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->setMessage('');

        $progress->start();
        $models->each(function (StripeProduct $model) use ($progress): void {
            $progress->setMessage(
                "Pushing <info>{$model->name}</info> (product <info>{$model->stripe_product_id}</info>)",
            );
            $model->pushToStripe($this->option('intent'));
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
