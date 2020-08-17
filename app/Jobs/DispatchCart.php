<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Railgun\Billing\Cart\Cart;
use Railgun\Models\Acquisition;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use function array_merge;
use function resolveMorph;

class DispatchCart extends Job
{
    protected Cart    $cart;
    protected int     $recipientId;
    protected ?string $transactionId;

    public function __construct(Cart $cart, int $recipientId, ?string $transactionId = null)
    {
        $this->cart          = $cart;
        $this->recipientId   = $recipientId;
        $this->transactionId = $transactionId;
    }

    public function handle(): void
    {
        $products = new Collection();

        $this->cart->products
            ->each(static function (Sellable $product) use ($products): void {
                $products->push([
                    'product_type' => resolveMorph($product),
                    'product_id'   => $product->getKey(),
                ]);

                if (!($product instanceof Pack)) {
                    return;
                }

                $product->trainings
                    ->pluck('id')
                    ->each(static function (int $trainingId) use ($products): void {
                        $products->push([
                            'product_type' => resolveMorph(Training::class),
                            'product_id'   => $trainingId,
                        ]);
                    });
            });

        $products
            ->map(fn(array $data) => array_merge($data, [
                'user_id'        => $this->recipientId,
                'transaction_id' => $this->transactionId,
            ]))
            ->map(static fn(array $data) => Acquisition::query()->updateOrCreate(
                Arr::only($data, ['user_id', 'product_type', 'product_id']),
                $data,
            ));
    }
}
