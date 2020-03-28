<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Railgun\Models\Acquisition;
use Railgun\Models\Training;
use Railgun\Models\User;
use RuntimeException;
use Stripe\Invoice;
use Stripe\StripeObject;
use function collect;

/**
 * Using a Stripe {@see Invoice}, deliver the corresponding products to a user.
 */
class DeliverProducts extends Job
{
    private Invoice $invoice;
    private User    $user;

    /**
     * @param Invoice $invoice The stripe Invoice.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->user = User::query()->where('stripe_id', '=', $this->invoice->customer)->firstOrFail();
    }

    /**
     * Deliver all the products in the invoice.
     */
    public function handle(): void
    {
        if (!$this->invoice->paid) {
            // This should never happen
            throw new RuntimeException('We wont deliver non-paid invoice');
        }

        $items = collect($this->invoice->lines->data)
            ->pluck('metadata')
            ->map(static fn(StripeObject $item) => $item->toArray());

        $validator = Validator::make($items->toArray(), [
            '*.user_id'      => 'required|exists:users,id',
            '*.product_type' => 'required|in:pack,training,premium',
            '*.product_id'   => 'required',
        ]);

        if ($validator->fails()) {
            // TODO: report validation failure?
            return;
        }

        $items->each(function ($item): void {
            switch ($item['product_type']) {
                case 'pack':
                    $this->deliverPack((int)$item['product_id']);
                    break;
                case 'training':
                    $this->deliverTraining((int)$item['product_id']);
                    break;
                case 'premium':
                    $this->deliverPremium((int)$item['end_date']);
                    break;
            }
        });
    }

    /**
     * Deliver a pack (which basically means delivering its products).
     *
     * @param int $packId The pack ID.
     */
    public function deliverPack(int $packId): void
    {
        $acquisition = new Acquisition([
            'user_id'        => $this->user->id,
            'product_type'   => 'pack',
            'product_id'     => $packId,
            'transaction_id' => $this->invoice->id,
        ]);
        $acquisition->save();

        Training::query()
            ->orWhereHas('packs', static function (Builder $builder) use ($packId): void {
                $builder->where('id', '=', $packId);
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
        $acquisition = new Acquisition([
            'user_id'        => $this->user->id,
            'product_type'   => 'training',
            'product_id'     => $trainingId,
            'transaction_id' => $this->invoice->id,
        ]);
        $acquisition->save();
    }

    /**
     * Deliver the premium product by setting the user premium_bypass_expires_at property.
     *
     * @param int $endDateTimestamp The timestamp which represents the end of the premium.
     */
    public function deliverPremium(int $endDateTimestamp): void
    {
        $this->user->premium_bypass_expires_at = Carbon::createFromTimestamp($endDateTimestamp);
        $this->user->save();
    }
}
