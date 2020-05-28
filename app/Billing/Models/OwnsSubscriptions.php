<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;
use function count;

/**
 * @property-read Collection|Subscription[] $subscriptions
 * ---
 * @property-read bool                      $premium
 * @mixin User
 */
trait OwnsSubscriptions
{
    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'customer_id', 'stripe_id');
    }

    /**
     * @return bool If the user is premium.
     */
    public function getPremiumAttribute(): bool
    {
        if (count($this->scopes) > 0) {
            // If the user has at least one scope, he is considered as premium
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
        return Cache::store('request')->rememberForever('user.' . $this->id . '.premium', function (): bool {
            $customerIds = [];

            if ($this->stripe_id !== null) {
                $customerIds[] = $this->stripe_id;
            }

            if ($this->braintree_id !== null) {
                $customerIds[] = $this->braintree_id;
            }

            return DB::table('subscriptions')
                ->whereIn('status', [StripeSubscription::STATUS_ACTIVE, StripeSubscription::STATUS_TRIALING])
                ->whereIn('customer_id', $customerIds)
                ->exists();
        });
    }
}
