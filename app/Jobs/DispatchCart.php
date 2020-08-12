<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Railgun\Billing\Cart\Cart;
use Railgun\Models\Acquisition;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use function array_merge;
use function get_class;

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
        $this->cart->products
            ->map(static function (Sellable $product) {
                $data = [
                    'product_type' => Relation::getMorphedModel(get_class($product)),
                    'product_id'   => $product->getKey(),
                ];

                if ($product instanceof Pack) {
                    $data = $product->trainings
                        ->pluck('id')
                        ->map(static fn(int $trainingId) => [
                            'product_type' => Relation::getMorphedModel(Training::class),
                            'product_id'   => $trainingId,
                        ])
                        ->prepend($data)
                        ->toArray();
                }

                return $data;
            })
            ->flatten()
            ->map(fn(array $data) => array_merge($data, [
                'user_id'        => $this->recipientId,
                'transaction_id' => $this->transactionId,
            ]))
            ->each(static function (array $data): void {
                Acquisition::query()->updateOrCreate(
                    Arr::only($data, ['user_id', 'product_type', 'product_id']),
                    Arr::only($data, ['transaction_id']),
                );
            });
    }
}
