<?php
namespace App\Models;

use App\Exceptions\Http401UnauthorizedException;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;


/**
 * App\Models\User
 *
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
 * @property int|null $class_id
 * @property string|null $stripe_id
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property string|null $trial_ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read bool $premium
 * @property-read int $daily_points
 * @property-read Class_ $class_
 * @property-read Collection|Client[] $clients
 * @property-read Collection|Comment[] $comments
 * @property-read Collection|Log[] $logs
 * @property-read Collection|Activity[] $meta
 * @property-read Collection|Subscription[] $subscriptions
 * @property-read Collection|Token[] $tokens
 * @property-read Collection|Vote[] $votes
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereClassId($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFacebookToken($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereToken($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePoints($value)
 * @method static Builder|User whereLevel($value)
 * @method static Builder|User whereDaily($value)
 * @method static Builder|User whereDailyAchievements($value)
 * @method static Builder|User whereSettings($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereCardBrand($value)
 * @method static Builder|User whereCardLastFour($value)
 * @method static Builder|User whereStripeId($value)
 * @method static Builder|User whereTrialEndsAt($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Training[] $trainings
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, Billable {
        HasApiTokens::tokenCan as passportTokenCan;
    }

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array The guarded attributes */
    protected $fillable = ["email", "username", "firstname", "lastname", "password", "points", "daily",
        "settings", "class_id"];

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
        "type" => "required|in:registered,moderator,admin",
        "token" => "required|size:36",
        "points" => "required|integer",
        "daily" => "nullable|in:10,20,40",
        "class_id" => "nullable|exists:classes,id"
    ];

    /** @var array The relations to include by default */
    protected $with = ["class_", "purchases"];

    /** @var array The virtual attributes */
    protected $appends = ["premium", "daily_points"];


    /**
     * Check if user is at least moderator
     *
     * @return bool
     */
    public function isModerator()
    {
        return $this->tokenCan("moderator");
    }


    /**
     * Determine if user can do something according to its token
     *
     * @param string $scope the minimum scope
     *
     * @return bool
     */
    public function tokenCan($scope)
    {
        $order = ["admin", "moderator", "registered"];
        $key = array_search($scope, $order);

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
     * Check if user is administrator
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->tokenCan("admin");
    }


    /**
     * premium attribute
     *
     * @return bool
     */
    public function getPremiumAttribute()
    {
        return $this->subscribed("premium");
    }


    /*
    |--------------------------------------------------------------------------
    | Passport
    |--------------------------------------------------------------------------
    */


    /**
     * daily_points attribute
     *
     * @return int
     */
    public function getDailyPointsAttribute()
    {
        $logs = Log::where([
            ["event", "user_gathered_points"],
            ["user_id", $this->id],
        ])
            ->whereBetween("created_at", [Carbon::now()->setTime(0, 0, 0), Carbon::now()])
            ->get();

        $sum = 0;

        /** @var Log $log */
        foreach ($logs as $log) {
            $sum += $log->value["points"];
        }

        return $sum;
    }


    /**
     * Check if user is authorized to do something; if not throw an exception
     *
     * @see Authorizable
     *
     * @param $ability
     * @param array $arguments
     * @param string $exceptionClass
     */
    public function canOrFail($ability, $arguments = [], $exceptionClass = Http401UnauthorizedException::class)
    {
        if ($this->cant($ability, $arguments) && class_exists($exceptionClass)) {
            throw new $exceptionClass();
        }
    }


    /**
     * Update customer card or create the Stripe customer if the user is not already a Stripe customer
     *
     * @param string $stripeToken
     */
    public function updateCardOrCreateStripeCustomer(string $stripeToken)
    {
        if (!$this->hasStripeId()) {
            $this->createAsStripeCustomer($stripeToken);
        } else {
            $this->updateCard($stripeToken);
        }
    }


    /**
     * @param int $purchaseId
     * @param string $purchaseType
     *
     * @return bool
     */
    public function hasPurchased(int $purchaseId, string $purchaseType): bool
    {
        return User::whereHas("purchases", function (Builder $query) use ($purchaseId, $purchaseType) {
            $query->where([
                ["purchasable_id", "=", $purchaseId],
                ["purchasable_type", "=", "training"]
            ]);
        })->where("id", "=", $this->id)->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * BelongsTo Class_
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "class_id");
    }


    /**
     * HasMany Comment
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    /**
     * HasMany Log
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }


    /**
     * HasMany Activity
     *
     * @return HasMany
     */
    public function meta(): HasMany
    {
        return $this->hasMany(Activity::class);
    }


    /**
     * HasMany Purchase
     *
     * @return HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }


    /**
     * HasMany Vote
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

}
