<?php

namespace App\Models;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\Traits\BraintreeCustomer;
use App\Models\Traits\StripeCustomer;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;

/**
 * Model User.
 *
 * @since     1.0.0
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int                                                   $id
 * @property string|null                                           $username
 * @property string                                                $email
 * @property string|null                                           $firstname
 * @property string|null                                           $lastname
 * @property string                                                $password
 * @property string                                                $type
 * @property string                                                $token
 * @property int                                                   $points
 * @property int                                                   $level
 * @property int                                                   $daily
 * @property int                                                   $daily_achievements
 * @property array                                                 $settings
 * @property int|null                                              $class_id
 * @property string|null                                           $braintree_id
 * @property string|null                                           $stripe_id
 * @property Carbon                                                $created_at
 * @property Carbon                                                $updated_at
 * @property \Braintree_Customer|null                              $braintree
 * @property bool                                                  $premium
 * @property int                                                   $daily_points
 * @property Class_                                                $class_
 * @property Collection|Client[]                                   $clients
 * @property Collection|Comment[]                                  $comments
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property Collection|Log[]                                      $logs
 * @property Collection|Meta[]                                     $meta
 * @property Collection|Product[]                                  $products
 * @property Collection|Purchase[]                                 $purchases
 * @property Collection|Token[]                                    $tokens
 * @property Collection|Vote[]                                     $votes
 *
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereToken($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User wherePoints($value)
 * @method static Builder|User whereLevel($value)
 * @method static Builder|User whereDaily($value)
 * @method static Builder|User whereDailyAchievements($value)
 * @method static Builder|User whereSettings($value)
 * @method static Builder|User whereClassId($value)
 * @method static Builder|User whereBraintreeId($value)
 * @method static Builder|User whereStripeId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, Notifiable, BraintreeCustomer, StripeCustomer {
        HasApiTokens::tokenCan as passportTokenCan;
    }

    private const STRIPE_PLANS = ["v3_monthly", "v3_annually", "v3_biannually"];
    private const BRAINTREE_PLANS = ["v4_monthly", "v4_annually", "v4_biannually"];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array The guarded attributes */
    protected $fillable = ["email", "username", "firstname", "lastname", "password", "points", "daily", "settings",
        "class_id"];
    /** @var array The hidden attributes from the model"s JSON */
    protected $hidden = ["password", "token"];
    /** @var array The attributes that should be cast to native types */
    protected $casts = ["settings" => "array"];
    /** @var array The validation rules on create */
    protected $rules = [
        "email" => "required|email|unique:users,email|max:60",
        "username" => "nullable|max:60",
        "firstname" => "nullable|max:60",
        "lastname" => "nullable|max:60",
        "password" => "required|min:3",
        "type" => "required|in:registered,redactor,moderator,admin,support",
        "token" => "required|size:36",
        "points" => "required|integer",
        "daily" => "nullable|in:10,20,40",
        "class_id" => "nullable|exists:classes,id",
    ];
    /** @var array The relations to include by default */
    protected $with = ["class_", "purchases"];
    /** @var array The virtual attributes */
    protected $appends = ["daily_points"];

    /*
    |--------------------------------------------------------------------------
    | Passport
    |--------------------------------------------------------------------------
    */

    /**
     * Chec if user is redactor.
     *
     * @return bool
     */
    public function isRedactor()
    {
        return $this->tokenCan("redactor");
    }

    /**
     * Determine if user can do something according to its token.
     *
     * @param string $scope the minimum scope
     *
     * @return bool
     */
    public function tokenCan($scope)
    {
        $order = ["admin", "moderator", "redactor", "registered"];
        $key = array_search($scope, $order, true);

        if ($key !== false) {
            foreach ($order as $k => $grantedScope) {
                if ($this->passportTokenCan($grantedScope) && $k <= $key) {
                    return true;
                }
            }
        }

        return $this->passportTokenCan($scope);
    }

    /**
     * Check if user is at least moderator.
     *
     * @return bool
     */
    public function isModerator()
    {
        return $this->tokenCan("moderator");
    }

    /**
     * Check if user is administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->tokenCan("admin");
    }

    /**
     * Check if user is administrator.
     *
     * @return bool
     */
    public function isSupport()
    {
        return $this->tokenCan("admin") || $this->tokenCan("support");
    }

    /**
     * Check if user is authorized to do something; if not throw an exception.
     *
     * @see Authorizable
     *
     * @param        $ability
     * @param array  $arguments
     * @param string $exceptionClass
     */
    public function canOrFail($ability, $arguments = [], $exceptionClass = Http401UnauthorizedException::class)
    {
        if ($this->cant($ability, $arguments) && class_exists($exceptionClass)) {
            throw new $exceptionClass();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Class_.
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "class_id");
    }

    /**
     * HasMany Comment.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * HasMany Log.
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    /**
     * HasMany Activity.
     *
     * @return HasMany
     */
    public function meta(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * HasMany Purchase.
     *
     * @return HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * HasMany Vote.
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * HasMany FamilyPack (as parent).
     *
     * @return HasMany
     */
    public function familyPacksParent()
    {
        return $this->hasMany(FamilyPack::class, "parent_id");
    }

    /**
     * daily_points attribute.
     *
     * @return int
     */
    public function getDailyPointsAttribute()
    {
        $logs = Log::where([
            ["event", "user_gathered_points"],
            ["user_id", $this->id]
        ])
            ->whereBetween("created_at", [
                Carbon::now()->setTime(0, 0, 0),
                Carbon::now()
            ])
            ->get();

        $sum = 0;

        /** @var Log $log */
        foreach ($logs as $log) {
            $sum += $log->value["points"];
        }

        return $sum;
    }

    /**
     * premium attribute.
     *
     * @throws Http400InvalidRequestException
     *
     * @return bool
     */
    public function getPremiumAttribute()
    {
        return $this->hasStripeSubscription(self::STRIPE_PLANS)
            || $this->hasBraintreeSubscription(self::BRAINTREE_PLANS)
            || $this->isChildOfAFamilyPack();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the user is a child of a family pack (allow to grant premium membership).
     *
     * @return bool
     */
    public function isChildOfAFamilyPack(): bool
    {
        $familyPacks = $this->familyPackChild()
            ->get();

        if ($familyPacks->isEmpty()) {
            return false;
        }
        /** @var FamilyPack $familyPack */
        $familyPack = $familyPacks->first(); // User should have only one family pack linked
        /** @var User $parent */
        $parent = self::where("id", "=", $familyPack->parent_id)
            ->firstOrFail();
        $parent->append(["premium", "braintree"]);

        return $parent->premium;
    }

    /**
     * HasMany FamilyPack (as child).
     *
     * @return HasMany
     */
    public function familyPackChild()
    {
        return $this->hasMany(FamilyPack::class, "child_id");
    }

    /**
     * braintree attribute.
     *
     * @return \Braintree\Customer|null
     */
    public function getBraintreeAttribute(): ?\Braintree_Customer
    {
        return $this->isBraintreeCustomer() ? $this->getBraintreeCustomer() : null;
    }

    /**
     * stripe attribute.
     *
     * @return \Stripe\Customer|null
     */
    public function getStripeAttribute(): ?\Stripe\Customer
    {
        return $this->isStripeCustomer() ? $this->getStripeCustomer() : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Products-related
    |--------------------------------------------------------------------------
    */

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPurchased(Product $product): bool
    {
        return $this->products()
            ->where("product_id", "=", $product->id)
            ->exists();
    }

    /**
     * HasManyThrough Product.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, "purchases");
    }
}
