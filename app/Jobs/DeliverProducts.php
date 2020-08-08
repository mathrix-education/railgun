<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Railgun\Billing\Stripe\Checkout\Cart;
use Railgun\Models\Acquisition;
use Railgun\Models\Training;
use Railgun\Models\User;
use RuntimeException;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;
use function collect;
use function config;

/**
 * Using a Stripe {@see Invoice}, deliver the corresponding products to a user.
 */
class DeliverProducts extends Job
{
    public Invoice $invoice;
    public User    $user;

    /**
     * @param Invoice $invoice The stripe Invoice.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Deliver all the products in the invoice.
     */
    public function handle(): void
    {
        try {
            /** @var User $user */
            $user = User::query()->where('stripe_id', '=', $this->invoice->customer)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            if (config('stripe.sandbox')) {
                // Allow user to not exists in test mode since we are using them for integration tests
                return;
            }

            throw $exception;
        }

        $this->user = $user;

        if ($this->invoice->metadata['type'] !== Cart::IDENTIFIER) {
            return;
        }

        if (!$this->invoice->paid) {
            // This should never happen
            throw new RuntimeException('We wont deliver non-paid invoice');
        }

        $items = collect($this->invoice->lines->data)
            ->map(static function (InvoiceLineItem $item) {
                $data = $item->metadata->toArray();

                if ($data['product_type'] === 'premium') {
                    $data['end_date'] = $item->period->end;
                }

                return $data;
            });

        $validator = Validator::make($items->toArray(), [
            '*.user_id'      => 'required|exists:users,id',
            '*.product_type' => 'required|in:chapter,pack,training,premium',
        ]);

        if ($validator->fails()) {
            // TODO: report validation failure?
            return; // @codeCoverageIgnore
        }

        $items->each(function ($item): void {
            switch ($item['product_type']) {
                case 'chapter':
                    $this->deliverChapter((int)$item['product_id']);
                    break;
                case 'pack':
                    $this->deliverPack((int)$item['product_id']);
                    break;
                case 'training':
                    $this->deliverTraining((int)$item['product_id']);
                    break;
            }
        });
    }

    /**
     * Deliver a chapter by creating a new {@see Acquisition}.
     *
     * @param int $chapterId The chapter ID.
     */
    public function deliverChapter(int $chapterId): void
    {
        Acquisition::query()->updateOrCreate(
            [
                'user_id'      => $this->user->id,
                'product_type' => 'chapter',
                'product_id'   => $chapterId,
            ],
            [
                'transaction_id' => $this->invoice->id,
            ],
        );
    }

    /**
     * Deliver a pack (which basically means delivering its products).
     *
     * @param int $packId The pack ID.
     */
    public function deliverPack(int $packId): void
    {
        Acquisition::query()->updateOrCreate(
            [
                'user_id'      => $this->user->id,
                'product_type' => 'pack',
                'product_id'   => $packId,
            ],
            [
                'transaction_id' => $this->invoice->id,
            ],
        );

        Training::query()
            ->orWhereHas('packs', static function (Builder $builder) use ($packId): void {
                $builder->where('packs.id', '=', $packId);
            })
            ->pluck('id')
            ->each(fn(int $trainingId) => $this->deliverTraining($trainingId));
    }

    /**
     * Deliver a training by creating a new {@see Acquisition}.
     *
     * @param int $trainingId The training ID.
     */
    public function deliverTraining(int $trainingId): void
    {
        Acquisition::query()->updateOrCreate(
            [
                'user_id'      => $this->user->id,
                'product_type' => 'training',
                'product_id'   => $trainingId,
            ],
            [
                'transaction_id' => $this->invoice->id,
            ],
        );
    }
}
