<?php

declare(strict_types=1);

namespace Railgun\Models;

use Railgun\Models\Traits\FacebookUser;
use Railgun\Services\Bishop\Subscription;
use Railgun\Models\Traits\Randomizable;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Auth\Authorizable;
use Mathrix\Lumen\JWT\Auth\HasJWT;
use stdClass;
use Stripe\Subscription as StripeSubscription;
use function array_merge;
use function floor;
use function in_array;
use function scope;
use function trim;

/**
 * @property-read int              $id
 * @property string|null           $username
 * @property string                $email
 * @property string|null           $firstname
 * @property string|null           $lastname
 * @property string                $password
 * @property string[]              $scopes
 * @property string                $token
 * @property bool                  $premium_bypass
 * @property Carbon                $premium_bypass_expires_at
 * @property int|null              $grade_id
 * @property string|null           $stripe_id
 * @property string|null           $braintree_id
 * @property array                 $settings
 * @property array                 $metadata
 * @property-read  Carbon          $created_at
 * @property-read  Carbon          $updated_at
 * ---
 * @property-read string           $full_name
 * @property-read bool             $premium
 * @property-read string           $provider
 * ---
 * @property Grade                 $grade
 * @property Collection|Comment[]  $comments
 * @property Collection|Product[]  $products
 * @property Collection|Purchase[] $purchases
 * @property Collection|Vote[]     $votes
 * ---
 * @method static Builder scope(string $scope)
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Randomizable;
    use Authorizable;
    use Authenticatable;
    use HasJWT;
    use FacebookUser;

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
        'password',
        'token',
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
        'premium_bypass_expires_at' => 'date',
        'settings'                  => 'array',
        'metadata'                  => 'array',
    ];

    /** @var array The validation rules on create */
    protected $rules = [
        'username'                  => 'nullable|max:60',
        'firstname'                 => 'nullable|max:60',
        'lastname'                  => 'nullable|max:60',
        'password'                  => 'nullable|min:3',
        'token'                     => 'nullable|size:36',
        'grade_id'                  => 'nullable|exists:grades,id',
        'premium_bypass'            => 'boolean',
        'premium_bypass_expires_at' => 'nullable|date',
    ];

    /** @var array The virtual attributes */
    protected $appends = [
        'level',
        'provider',
        'last_session',
        'premium',
    ];

    /**
     * Custom validation rules
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return array_merge(
            $this->rules,
            [
                'email' => [
                    'required',
                    'email',
                    'max:60',
                    Rule::unique('users', 'email')
                        ->ignore($this->id),
                ],
            ]
        );
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        /** @var User $user */
        $user = Auth::user();

        // Give full read to authorized users or themselves
        if (scope('users:read') || $this->is($user)) {
            return;
        }

        // Restrict visible fields for unauthorized users
        $this->setVisible(['id', 'username', 'grade_id']);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user has purchased the given product.
     *
     * @param Product $product
     *
     * @return bool
     */
    public function hasPurchased(Product $product): bool
    {
        return $this->purchases()
            ->whereIn(
                'product_id',
                $product->lineage()
                    ->pluck('id')
            )
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

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
     * HasMany Mark.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * BelongsToMany Products using Purchase a pivot.
     * Reflects the products bought by the user.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'purchases')
            ->as('purchase')
            ->using(Purchase::class)
            ->withPivot(['id', 'transaction_id'])
            ->withTimestamps();
    }

    /**
     * HasMany Purchase.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * HasMany Vote.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

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
     * level attribute.
     */
    public function getLevelAttribute(): int
    {
        return (int)floor((($this->points ** (1 / 1.2)) / 10) + 1);
    }

    /**
     * premium attribute.
     */
    public function getPremiumAttribute(): bool
    {
        if ($this->premium_bypass) {
            // Permanent premium bypass
            return true;
        }

        if ($this->premium_bypass_expires_at !== null && $this->premium_bypass_expires_at->isFuture()) {
            // Temporary premium bypass
            return true;
        }

        if (!$this->isStripeCustomer() && !$this->isBraintreeCustomer()) {
            // Not Stripe nor Braintree customer
            return false;
        }

        // Search in subscriptions
        /** @var Subscription $subscription */
        foreach ($this->subscriptions() as $subscription) {
            $allowedStatuses = [
                StripeSubscription::STATUS_ACTIVE,
                StripeSubscription::STATUS_TRIALING,
            ];

            if (in_array($subscription->status, $allowedStatuses, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Subscription|BaseCollection
     */
    public function subscriptions(): BaseCollection
    {
        $subscriptions = new BaseCollection();

        if ($this->isBraintreeCustomer()) {
            foreach ($this->getBraintreeSubscriptions() as $braintreeSubscription) {
                $subscriptions->push(Subscription::from($braintreeSubscription));
            }
        }

        if ($this->isStripeCustomer()) {
            foreach ($this->getStripeSubscriptions() as $stripeSubscription) {
                $subscriptions->push(Subscription::from($stripeSubscription));
            }
        }

        return $subscriptions;
    }

    /**
     * The login provider.
     */
    public function getProviderAttribute(): string
    {
        $provider = 'api';

        if (isset($this->metadata['facebook']['id'])) {
            $provider = 'facebook';
        }

        return $provider;
    }

    /**
     * @return array|stdClass
     */
    public function getLastSessionAttribute()
    {
        if (empty($this->settings['last_session'])) {
            return [];
        }

        $body        = [];
        $lastSession = $this->settings['last_session'];

        foreach ($lastSession as $moduleId) {
            /** @var Module $module */
            $module = Module::query()
                ->findOrFail($moduleId);
            /** @var Activity $activity */
            $activity = Activity::query()
                ->findOrFail($module->activity_id);
            /** @var Notion $notion */
            $notion = Notion::query()
                ->findOrFail($activity->notion_id);
            /** @var Chapter $chapter */
            $chapter = Chapter::query()
                ->findOrFail($notion->chapter_id);
            /** @var Discipline $discipline */
            $discipline = Discipline::query()
                ->findOrFail($chapter->discipline_id);
            /** @var Grade $grade */
            $grade = Grade::query()
                ->findOrFail($chapter->grade_id);

            $body[] = [
                'grade_slug' => $grade->slug,
                'discipline' => $discipline,
                'chapter'    => $chapter,
                'activity'   => $activity,
                'module'     => $module,
            ];
        }

        return $body;
    }
}
