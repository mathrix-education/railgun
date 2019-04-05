<?php

namespace App\Models;

use App\Models\Traits\BraintreeCustomer;
use App\Models\Traits\PermissionsTrait;
use App\Models\Traits\StripeCustomer;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class User.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 * @property int $id
 * @property string|null $username
 * @property string $email
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string $password
 * @property string $type
 * @property string $token
 * @property int $points
 * @property int $level
 * @property int $daily
 * @property int $daily_achievements
 * @property array $settings
 * @property bool $premium_bypass
 * @property int|null $grade_id
 * @property string|null $braintree_id
 * @property string|null $stripe_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property Grade $grade
 * @property bool $premium
 * @property int $daily_points
 * ---
 * @property Collection|Client[] $clients
 * @property Collection|Token[] $tokens
 * @property Collection|Comment[] $comments
 * @property Collection|DatabaseNotification[] $notifications
 * @property Collection|DatabaseNotification[] $unreadNotifications
 * @property Collection|DatabaseNotification[] $readNotifications
 * @property Collection|Log[] $logs
 * @property Collection|Product[] $products
 * @property Collection|Purchase[] $purchases
 * @property Collection|Vote[] $votes
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
    protected $fillable = [
        "email",
        "username",
        "firstname",
        "lastname",
        "password",
        "points",
        "daily",
        "settings",
        "grade_id"
    ];

    /** @var array The hidden attributes from the model"s JSON */
    protected $hidden = ["password", "token", "premium_bypass"];

    /** @var array The attributes that should be cast to native types */
    protected $casts = ["settings" => "array"];

    /** @var array The validation rules on create */
    protected $rules = [
        "username" => "nullable|max:60",
        "firstname" => "nullable|max:60",
        "lastname" => "nullable|max:60",
        "password" => "required|min:3",
        "type" => "required|in:registered,redactor,moderator,admin,support",
        "token" => "nullable|size:36",
        "points" => "required|integer",
        "daily" => "nullable|in:10,20,40",
        "grade_id" => "nullable|exists:grades,id",
    ];

    /** @var array The relations to include by default */
    protected $with = ["grade", "purchases"];

    /** @var array The virtual attributes */
    protected $appends = ["daily_points"];


    /**
     * Custom validation rules
     *
     * @return array
     */
    public function getValidationRules()
    {
        return array_merge($this->rules, [
            "email" => ["required", "email", "max:60", Rule::unique("users", "email")->ignore($this->id)],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Record an event linked to this user.
     *
     * @param string $service The service.
     * @param string $event The event key.
     * @param string $status The status.
     * @param array $payload The record payload.
     *
     * @return Record
     * @throws Http400BadRequestException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function record(string $service, string $event, string $status, array $payload)
    {
        return Record::record($this, $service, $event, $status, $payload);
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


    /**
     * Get users scopes, space-separated.
     *
     * @return string
     */
    public function getPassportScopes()
    {
        switch ($this->type) {
            case "admin":
                $scopes = "admin moderator redactor registered support";
                break;
            case "moderator":
                $scopes = "moderator redactor registered";
                break;
            case "redactor":
                $scopes = "redactor registered";
                break;
            case "registered":
                $scopes = "registered";
                break;
            case "support":
                $scopes = "registered support";
                break;
            default:
                $scopes = "";
                break;
        }

        return $scopes;
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Grade.
     *
     * @return BelongsTo
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
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
     * HasMany Mark.
     *
     * @return HasMany
     */
    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
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
     * BelongsToMany Product.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, "purchases");
    }


    /**
     * HasMany Records.
     *
     * @return HasMany
     */
    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
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
        $logs = Log::query()
            ->where([
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
     * @return bool
     * @throws Http400BadRequestException
     *
     */
    public function getPremiumAttribute()
    {
        return $this->premium_bypass
            || $this->hasBraintreeSubscription(self::BRAINTREE_PLANS)
            || $this->hasStripeSubscription(self::STRIPE_PLANS);
    }
}
