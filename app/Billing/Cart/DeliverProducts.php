<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

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

class DeliverProducts
{
    private Invoice $invoice;
    private User    $user;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->user = User::query()->where('stripe_id', '=', $this->invoice->customer)->firstOrFail();
    }

    public function deliver(): void
    {
        if (!$this->invoice->paid) {
            throw new RuntimeException('We wont deliver non-paid invoice');
        }

        $items = collect($this->invoice->lines->data)
            ->pluck('metadata')
            ->map(fn(StripeObject $item) => $item->toArray());

        $validator = Validator::make($items->toArray(), [
            '*.user_id'      => 'required|exists:users,id',
            '*.product_type' => 'required|in:pack,training,premium',
            '*.product_id'   => 'required',
        ]);

        if ($validator->fails()) {
            return;
        }

        $items->each(function ($item) {
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
            ->orWhereHas('packs', static function (Builder $builder) use ($packId) {
                $builder->where('id', '=', $packId);
            })
            //->get(['id'])
            ->pluck('id')
            ->each(fn(int $trainingId) => $this->deliverTraining($trainingId));
    }

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

    public function deliverPremium(int $endDateTimestamp): void
    {
        $this->user->premium_bypass_expires_at = Carbon::createFromTimestamp($endDateTimestamp);
        $this->user->save();
    }
}
