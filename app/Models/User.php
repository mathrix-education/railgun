<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Railgun\Billing\Models\StripeCustomer;
use Railgun\Models\Traits\Cacheable;
use Railgun\Models\Traits\Randomizable;
use Ramsey\Uuid\Uuid;
use Stripe\Subscription as StripeSubscription;
use function trim;

/**
 * @property-read int                       $id
 * @property string                         $uuid
 * @property string|null                    $username
 * @property string                         $email
 * @property string|null                    $firstname
 * @property string|null                    $lastname
 * @property string                         $password
 * @property string[]                       $scopes
 * @property bool                           $premium_bypass
 * @property Carbon                         $premium_bypass_expires_at
 * @property int|null                       $grade_id
 * @property string|null                    $stripe_id
 * @property string|null                    $braintree_id
 * @property array                          $settings
 * @property array                          $metadata
 * @property-read  Carbon                   $created_at
 * @property-read  Carbon                   $updated_at
 * ---
 * @property-read string                    $full_name
 * @property-read bool                      $premium
 * @property-read string                    $provider
 * ---
 * @property-read Grade                     $grade
 * @property-read Collection|Comment[]      $comments
 * @property-read Collection|Vote[]         $votes
 * @property-read Collection|Subscription[] $subscriptions
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use Cacheable;
    use Randomizable;
    use StripeCustomer;

    /** @var array The guarded attributes */
    protected $fillable = [
        'email',
        'username',
        'firstname',
        'lastname',
        'password',
        'grade_id',
    ];

    /** @var array The hidden attributes from the model's JSON */
    protected $hidden = [
        'uuid',
        'password',
        'premium_bypass',
        'premium_bypass_expires_at',
        'braintree_id',
        'metadata',
    ];

    protected $attributes = [
        'username'                  => null,
        'firstname'                 => null,
        'lastname'                  => null,
        'scopes'                    => '[]',
        'premium_bypass'            => false,
        'premium_bypass_expires_at' => null,
        'settings'                  => '[]',
    ];

    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        'scopes'                    => 'array',
        'premium_bypass'            => 'boolean',
        'premium_bypass_expires_at' => 'datetime',
        'settings'                  => 'array',
        'metadata'                  => 'array',
    ];

    /** @var array The virtual attributes */
    protected $appends = [
        'provider',
        'last_session',
    ];

    public function __construct(array $attributes = [])
    {
        $this->attributes['uuid'] = Uuid::uuid4();
        parent::__construct($attributes);
    }

    /**
     * BelongsTo Grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * HasMany Comment.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * HasMany Vote.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function packs(): MorphToMany
    {
        return $this->morphedByMany(Pack::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    public function trainings(): MorphToMany
    {
        return $this->morphedByMany(Training::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'customer_id', 'stripe_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }
    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return bool If the user is premium.
     */
    public function getPremiumAttribute(): bool
    {
        if ($this->premium_bypass) {
            return true;
        }

        if ($this->premium_bypass_expires_at instanceof Carbon && $this->premium_bypass_expires_at->isFuture()) {
            return true;
        }

        // Raw query to speed things up
        return Cache::store('array')->remember('user.' . $this->id . '.premium', 5, function (): bool {
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

    /**
     * @param string $password The new password.
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * full_name attribute.
     */
    public function getFullNameAttribute(): string
    {
        if (empty($this->firstname) && empty($this->lastname)) {
            return $this->username;
        }

        return trim("{$this->firstname} {$this->lastname}");
    }

    /**
     * The login provider.
     */
    public function getProviderAttribute(): string
    {
        $provider = 'api';

        if (isset($this->metadata['facebook']['id'])) {
            $provider = 'facebook';
        } elseif (isset($this->metadata['google']['id'])) {
            $provider = 'google';
        }

        return $provider;
    }
}
