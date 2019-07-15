<?php

namespace App\Services\Braintree\Models;

use App\Exceptions\BraintreeErrorHandler;
use App\Exceptions\GatewayRejectionException;
use App\Exceptions\InvalidSubscriptionStatusException;
use App\Exceptions\ProcessorDeclinedException;
use App\Exceptions\UnknownBraintreeException;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\Exception\NotFound as BraintreeNotFoundException;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Zero\Exceptions\Models\Models\ModelNotFoundException;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function gateway;

/**
 * Class Subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 0.9.7
 *
 * @property string $id
 * @property string $status
 * @property float $price
 * @property float $next_billing_amount
 * @property int $current_billing_cycle
 * @property int $number_of_billing_cycles
 * @property Carbon $billing_period_start_date
 * @property Carbon $billing_period_end_date
 * @property Carbon $next_billing_date
 * @property array $metadata
 * @property int $customer_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $ended_at
 * @property-read BraintreeSubscription $subscription
 * @property-read User $user
 */
class Subscription extends BaseModel
{
    // Status
    public const ACTIVE = BraintreeSubscription::ACTIVE;
    public const CANCELED = BraintreeSubscription::CANCELED;
    public const EXPIRED = BraintreeSubscription::EXPIRED;
    public const WILL_EXPIRE = "Will Expire";
    public const PAST_DUE = BraintreeSubscription::PAST_DUE;
    public const PENDING = BraintreeSubscription::PENDING;

    public $incrementing = false;
    protected $fillable = [
        "id",
        "status",
        "price",
        "current_billing_cycle",
        "number_of_billing_cycles",
        "next_billing_amount",
        "billing_period_start_date",
        "billing_period_end_date",
        "next_billing_date",
        "customer_id",
        "ended_at",
        "created_at",
        "updated_at"
    ];
    protected $dates = [
        "billing_period_start_date",
        "billing_period_end_date",
        "next_billing_date",
        "ended_at"
    ];

    /** @var BraintreeSubscription $_subscription */
    private $_subscription;


    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "customer_id", "braintree_id");
    }


    /**
     * subscription field getter
     * Retrieve Braintree Subscription object.
     *
     * @return BraintreeSubscription|null
     * @throws ModelNotFoundException
     */
    public function getSubscriptionAttribute(): BraintreeSubscription
    {
        $this->_subscription = $this->asBraintreeSubscription();

        return $this->_subscription;
    }


    /**
     * @return BraintreeSubscription
     * @throws ModelNotFoundException
     */
    public function asBraintreeSubscription(): BraintreeSubscription
    {
        try {
            return gateway()->subscription()->find($this->id);
        } catch (BraintreeNotFoundException $e) {
            throw new ModelNotFoundException(["id" => $this->id], null, $e);
        }
    }


    /**
     * Convert a Braintree Subscription to Eloquent Subscription.
     *
     * @param BraintreeSubscription $subscription The Braintree Subscription.
     * @param User $user The user. If not provided, we will try to get him from Braintree.
     *
     * @return array
     */
    public static function toEloquent(BraintreeSubscription $subscription, User $user = null): array
    {
        if ($user === null) {
            /** @var Subscription $existingSubscription */
            $existingSubscription = Subscription::query()
                ->where("id", "=", $subscription->id)
                ->first();

            if ($existingSubscription !== null) {
                $user = $existingSubscription->user;
            } else {
                $customerId = SubscriptionUtils::getAssociatedCustomerId($subscription);
                $user = User::query()->where("braintree_id", "=", $customerId)->first();
            }
        }

        $data = [
            "id" => $subscription->id,
            "status" => $subscription->currentBillingCycle === $subscription->numberOfBillingCycles
                ? Subscription::WILL_EXPIRE : $subscription->status,
            "price" => floatval($subscription->price),
            "current_billing_cycle" => $subscription->currentBillingCycle,
            "number_of_billing_cycles" => $subscription->numberOfBillingCycles,
            "next_billing_amount" => floatval($subscription->nextBillingPeriodAmount),
            "billing_period_start_date" => $subscription->billingPeriodStartDate,
            "billing_period_end_date" => $subscription->billingPeriodEndDate,
            "next_billing_date" => $subscription->nextBillingDate,
            "customer_id" => $user !== null ? $user->braintree_id : null,
            "created_at" => $subscription->createdAt,
            "updated_at" => $subscription->updatedAt
        ];

        return $data;
    }


    /**
     * Create a new subscription.
     *
     * @param BraintreeSubscription $subscription
     * @param User|null $user
     *
     * @return Subscription
     */
    public static function createFromBraintreeSubscription(BraintreeSubscription $subscription, User $user = null): self
    {
        /** @var Subscription $instance */
        $instance = self::query()->firstOrNew(["id" => $subscription->id]);
        $instance->fill(self::toEloquent($subscription, $user));
        $instance->save();

        return $instance;
    }


    /**
     * Pull the Subscription from the Braintree gateway.
     *
     * @throws ModelNotFoundException
     */
    public function sync(): void
    {
        $this->fill(self::toEloquent($this->asBraintreeSubscription(), $this->user));
        $this->save();
    }


    /**
     * Soft-cancel (cancel) a subscription, which will schedule its expiration.
     * Soft-cancelled subscriptions maybe be resumed before their expiration.
     *
     * $this->save();
     *
     * @throws GatewayRejectionException
     * @throws InvalidSubscriptionStatusException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public function cancel(): void
    {
        $allowedStatuses = [self::ACTIVE];

        if (!in_array($this->status, $allowedStatuses)) {
            throw new InvalidSubscriptionStatusException($this, $allowedStatuses);
        }

        if (!$this->next_billing_date->isFuture()) {
            // Standard subscription, we can set the numberOfBillingCycles = currentBillingCycle
            /** @var BraintreeResponse $result */
            $result = gateway()->subscription()->update($this->id, [
                "numberOfBillingCycles" => $this->current_billing_cycle
            ]);
        } else {
            // "Trial subscription", we cancel the subscription immediately and setup the "premium bypass"
            $result = gateway()->subscription()->cancel($this->id);
            $this->user->premium_bypass_expires_at = $this->next_billing_date;
            $this->user->save();
        }

        if (!$result->success) {
            BraintreeErrorHandler::report($result);
        }

        $this->status = self::WILL_EXPIRE;
        $this->updated_at = $result->subscription->updatedAt;
        $this->ended_at = $this->next_billing_date;
        $this->save();
    }


    /**
     * Hard-cancel (terminate) a subscription immediately.
     *
     * @throws GatewayRejectionException
     * @throws InvalidSubscriptionStatusException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public function terminate(): void
    {
        $allowedStatuses = [self::ACTIVE, self::WILL_EXPIRE, self::PENDING];

        if (!in_array($this->status, $allowedStatuses)) {
            throw new InvalidSubscriptionStatusException($this, $allowedStatuses);
        }

        /** @var BraintreeResponse $result */
        $result = gateway()->subscription()->cancel($this->id);

        if (!$result->success) {
            BraintreeErrorHandler::report($result);
        }

        $this->fill(self::toEloquent($result->subscription, $this->user));
        $this->status = self::CANCELED;
        $this->updated_at = $result->subscription->updatedAt;
        $this->ended_at = now();
        $this->save();
    }
}
