<?php

namespace App\Services\Braintree\Models;

use App\Exceptions\AlreadyCustomerException;
use App\Exceptions\BraintreeErrorHandler;
use App\Exceptions\GatewayRejectionException;
use App\Exceptions\InvalidCustomerException;
use App\Exceptions\LockedCustomerException;
use App\Exceptions\ProcessorDeclinedException;
use App\Exceptions\UnknownBraintreeException;
use App\Models\Product;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Utils\CustomerUtils;
use Braintree\CreditCard;
use Braintree\Customer as BraintreeCustomer;
use Braintree\Exception\NotFound;
use Braintree\PayPalAccount;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships as HasDeepRelationships;
use function gateway;

/**
 * Trait Customer.
 * Since our Braintree customers are also our users, we have to declare it a a Trait, which is used by the User model.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 *
 * @property BraintreeCustomer $customer
 *
 * @property Subscription[]|Collection $subscriptions
 * @property Transaction[]|Collection $transactions
 *
 * @mixin User
 */
trait Customer
{
    use HasDeepRelationships;

    /** @var BraintreeCustomer|null */
    private $_customer = null;


    /**
     * Fetch the Customer from the Braintree gateway. If it cannot be found, return null.
     *
     * @param bool $ignoreCached Ignore the cached version, if it exists.
     *
     * @return BraintreeCustomer|null
     */
    public function asCustomer($ignoreCached = true): ?BraintreeCustomer
    {
        try {
            if ($this->_customer !== null && !$ignoreCached) {
                return $this->_customer;
            } else {
                return gateway()->customer()->find($this->braintree_id);
            }
        } catch (NotFound $e) {
            $this->braintree_id = null;

            return null;
        }
    }


    /**
     * customer field getter
     * Retrieve the Customer object with Braintree SDK. Maybe null if not found.
     *
     * @return BraintreeCustomer|null
     */
    public function getCustomerAttribute(): ?BraintreeCustomer
    {
        $this->_customer = $this->asCustomer();

        return $this->_customer;
    }


    /**
     * customer field setter
     *
     * @param BraintreeCustomer $customer
     */
    public function setCustomerAttribute(BraintreeCustomer $customer): void
    {
        $this->_customer = $customer;
    }


    /**
     * Check if the user is a Braintree customer.
     *
     * @param bool $strict If strict mode is on, we will try to fetch the user from the Braintree API.
     *
     * @return bool
     */
    public function isCustomer($strict = false): bool
    {
        if (isset($this->_customer) && $this->_customer instanceof BraintreeCustomer) {
            // Short circuit the getter, since we do not want to fetch the user at this time
            return true;
        } else if ($this->braintree_id === null) {
            // No braintree_id = cannot be a valid customer
            return false;
        } else if ($strict) {
            // Try to load the customer for strict checking
            return $this->asCustomer() instanceof BraintreeCustomer;
        } else {
            return true;
        }
    }


    /**
     * Check that the user is a valid Braintree customer, or fail.
     *
     * @param bool $strict If strict mode is on, we will try to fetch the user from the Braintree API.
     *
     * @throws InvalidCustomerException
     */
    public function requireCustomer($strict = false): void
    {
        if (!$this->isCustomer($strict)) {
            throw new InvalidCustomerException($this);
        }
    }


    /**
     * Create a new Braintree customer from the current user.
     *
     * @param string|null $paymentMethodNonce If given, attach a payment method.
     *
     * @throws AlreadyCustomerException
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public function createCustomer(?string $paymentMethodNonce = null): void
    {
        if ($this->isCustomer(true)) {
            throw new AlreadyCustomerException($this);
        }

        $payload = [
            "id" => CustomerUtils::generateId(),
            "firstName" => $this->firstname,
            "lastName" => $this->lastname,
            "email" => $this->email
        ];

        if ($paymentMethodNonce !== null) {
            $payload = array_merge($payload, [
                "paymentMethodNonce" => $paymentMethodNonce,
                "creditCard" => [
                    "options" => [
                        "verifyCard" => true
                    ]
                ]
            ]);
        }

        /** @var BraintreeResponse $result */
        $result = gateway()->customer()->create($payload);

        if (!$result->success || !isset($result->customer)) {
            BraintreeErrorHandler::report($result, $this);
        }

        $this->braintree_id = $result->customer->id;
        $this->save();
        $this->customer = $result->customer;
    }


    /**
     * Push the customer (first name, last name and email) to the Braintree gateway.
     *
     * @throws GatewayRejectionException
     * @throws InvalidCustomerException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public function pushCustomer(): void
    {
        $this->requireCustomer(true);

        // From DB => Braintree
        /** @var BraintreeResponse $result */
        $result = gateway()->customer()->update($this->braintree_id, [
            "firstName" => $this->firstname,
            "lastName" => $this->lastname,
            "email" => $this->email
        ]);

        if (!$result->success) {
            BraintreeErrorHandler::report($result, $this);
        }
    }


    /**
     * Pull the customer (first name, last name and email) from the Braintree gateway.
     *
     * @throws InvalidCustomerException
     */
    public function pullCustomer(): void
    {
        $this->requireCustomer(true);
        $this->firstname = $this->customer->firstName;
        $this->lastname = $this->customer->lastName;
        $this->email = $this->customer->email;
        $this->save();
    }


    /**
     * Get the customer default payment method. May return null if the customer has no payment method.
     *
     * @return CreditCard|PayPalAccount|null
     * @throws InvalidCustomerException
     */
    public function defaultPaymentMethod()
    {
        $this->requireCustomer();

        return $this->customer->defaultPaymentMethod();
    }


    /**
     * Check if the user is locked (to avoid subscriptions duplicate).
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return isset($this->settings["locked"]) && $this->settings["locked"] === true;
    }


    /**
     * Assert that the user is not locked.
     *
     * @throws LockedCustomerException
     */
    public function requireUnlocked(): void
    {
        if ($this->isLocked()) {
            throw new LockedCustomerException($this);
        }
    }


    /**
     * Lock user customer.
     *
     * @param Closure|null $whileLocked If given, lock the user during the closur execution.
     *
     * @return void|mixed
     */
    public function lock(Closure $whileLocked = null)
    {
        $this->settings = array_replace($this->settings, ["locked" => true]);
        $this->save();

        if ($whileLocked !== null) {
            $result = $whileLocked($this);
            $this->unlock();

            return $result;
        }
    }


    /**
     * Unlock the user.
     */
    public function unlock(): void
    {
        $this->settings = array_replace($this->settings, ["locked" => false]);
        $this->save();
    }


    /**
     * If teh user can pay (i.e. is a Braintree customer and have a payment method).
     *
     * @return bool
     * @throws InvalidCustomerException
     */
    public function canPay(): bool
    {
        return $this->isCustomer(true) && $this->defaultPaymentMethod() !== null;
    }


    /**
     * User HasMany Subscription
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, "customer_id", "braintree_id");
    }


    /**
     * Get the current active subscription.
     *
     * @return Subscription|null
     */
    public function getActiveSubscription(): ?Subscription
    {
        foreach ($this->subscriptions as $subscription) {
            if (in_array($subscription->status, [Subscription::ACTIVE, Subscription::WILL_EXPIRE])) {
                return $subscription;
            }
        }

        return null;
    }


    /**
     * @return HasMany transactions
     *
     * @see Transaction
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, "customer_id", "braintree_id");
    }


    /**
     * HasManyDeep products through Transaction -> LineItem -> Product
     *
     * @see Transaction
     * @see LineItem
     * @see Product
     */
    public function products(): HasManyDeep
    {
        return $this->hasManyDeep(
            Product::class,
            [
                Transaction::class,
                LineItem::class
            ],
            [
                "customer_id",
                "transaction_id",
                "id"
            ],
            [
                "braintree_id",
                "id",
                ["product_type", "product_code"]
            ]
        );
    }
}
