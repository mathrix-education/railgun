<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Railgun\Billing\Stripe\Models\Academy;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Subscription;
use Railgun\Models\Traits\Cacheable;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;
use function array_keys;
use function array_values;
use function count;

/**
 * @property-read Collection|Subscription[] $subscriptions
 * ---
 * @property-read bool                      $premium
 * @property-read bool                      $academy
 * @mixin User
 */
trait OwnsSubscriptions
{
    use Cacheable;

    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return bool If the user is premium.
     */
    public function getPremiumAttribute(): bool
    {
        if (count($this->permissions) > 0) {
            // If the user has at least one permission, he is considered as premium.
            return true;
        }

        if ($this->premium_bypass) {
            // Permanent premium bypass
            return true;
        }

        if ($this->premium_bypass_expires_at instanceof Carbon && $this->premium_bypass_expires_at->isFuture()) {
            // Temporary premium_bypass
            return true;
        }

        // Cache for the current request
        return $this->getCache()->remember('premium', 3600, function (): bool {
            $customerIds = [];

            if ($this->stripe_id !== null) {
                $customerIds[] = $this->stripe_id;
            }

            if ($this->braintree_id !== null) {
                $customerIds[] = $this->braintree_id;
            }

            return DB::table('subscriptions')
                ->whereIn('plan', [
                    ...ModeMachine::PLANS,
                    ...array_keys(ModeMachine::PLAN_MAPPINGS),
                    ...array_values(ModeMachine::PLAN_MAPPINGS),
                ])
                ->whereIn('status', [StripeSubscription::STATUS_ACTIVE, StripeSubscription::STATUS_TRIALING])
                ->whereIn('customer_id', $customerIds)
                ->exists();
        });
    }

    public function getAcademyAttribute(): bool
    {
        if (count($this->permissions) > 0) {
            // If the user has at least one permission, he is considered as premium.
            return true;
        }

        // Cache for the current request
        return $this->getCache()->remember('academy', 3600, function (): bool {
            $customerIds = [];

            if ($this->stripe_id !== null) {
                $customerIds[] = $this->stripe_id;
            }

            if ($this->braintree_id !== null) {
                $customerIds[] = $this->braintree_id;
            }

            return DB::table('subscriptions')
                ->whereIn('plan', Academy::PLANS)
                ->whereIn('status', [StripeSubscription::STATUS_ACTIVE, StripeSubscription::STATUS_TRIALING])
                ->whereIn('customer_id', $customerIds)
                ->exists();
        });
    }
}
