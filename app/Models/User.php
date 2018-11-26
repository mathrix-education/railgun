<?php

namespace App\Models;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Traits\BraintreeCustomer;
use App\Models\Traits\StripeCustomer;
use App\Models\UserTraits\PermissionsTrait;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification;
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
 * @property int                               $id
 * @property string|null                       $username
 * @property string                            $email
 * @property string|null                       $firstname
 * @property string|null                       $lastname
 * @property string                            $password
 * @property string                            $type
 * @property string                            $token
 * @property int                               $points
 * @property int                               $level
 * @property int                               $daily
 * @property int                               $daily_achievements
 * @property array                             $settings
 * @property bool                              $premium_bypass
 * @property int|null                          $class_id
 * @property int|null                          $family_pack_id
 * @property string|null                       $braintree_id
 * @property string|null                       $stripe_id
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 * @property \Braintree\Customer|null          $braintree
 * @property bool                              $premium
 * @property int                               $daily_points
 * @property Class_                            $class_
 * @property FamilyPack                        $familyPackAsParent
 * @property FamilyPack                        $familyPackAsChild
 * @property Collection|Client[]               $clients
 * @property Collection|Comment[]              $comments
 * @property Collection|DatabaseNotification[] $notifications
 * @property Collection|DatabaseNotification[] $unreadNotifications
 * @property Collection|DatabaseNotification[] $readNotifications
 * @property Collection|Log[]                  $logs
 * @property Collection|Meta[]                 $meta
 * @property Collection|Product[]              $products
 * @property Collection|Purchase[]             $purchases
 * @property Collection|Token[]                $tokens
 * @property Collection|Vote[]                 $votes
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable,
        Authorizable,
        HasApiTokens,
        Notifiable,
        BraintreeCustomer,
        StripeCustomer,
        PermissionsTrait {
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
    protected $hidden = ["password", "token", "premium_bypass"];

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
     * HasOne FamilyPack (as parent).
     *
     * @return HasOne
     */
    public function familyPackAsParent()
    {
        return $this->hasOne(FamilyPack::class, "parent_id");
    }

    /**
     * BelongsTo FamilyPack (as child).
     *
     * @return BelongsTo
     */
    public function familyPackAsChild()
    {
        return $this->belongsTo(FamilyPack::class, "family_pack_id");
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
     * BelongsToMany Product.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, "purchases");
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


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
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
        return $this->premium_bypass
            || $this->isChildOfAFamilyPack()
            || $this->hasBraintreeSubscription(self::BRAINTREE_PLANS)
            || $this->hasStripeSubscription(self::STRIPE_PLANS);
    }

    /**
     * Get the family pack id as parent.
     *
     * @return int
     */
    public function getFamilyPackIdAsParentAttribute(): ?int
    {
        if ($this->has("family_pack_as_parent")) {

        } else {
            return null;
        }
        return $this->familyPackAsParent !== null ? $this->familyPackAsParent->id : null;
    }

    /**
     * Append all required premium attributes
     */
    public function appendPremiumAttributes()
    {
        $this->append(["premium", "braintree", "stripe", "family_pack_id_as_parent"]);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Check if the user is a child of a family pack (allow to grant premium membership).
     *
     * @return bool
     */
    public function isChildOfAFamilyPack(): bool
    {
        return $this->familyPackAsChild !== null ? $this->familyPackAsChild->isActive() : false;
    }

    /**
     * Check if user has purchased the given product.
     *
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
}
