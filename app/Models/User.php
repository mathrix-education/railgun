<?php

namespace App\Models;

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
 * @property-read Class_ $class_
 * @property-read Collection|Activity[] $activities
 * @property-read Collection|Client[] $clients
 * @property-read Collection|Comment[] $comments
 * @property-read Collection|Log[] $logs
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
 * @mixin \Eloquent
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, Billable {
        HasApiTokens::tokenCan as passportTokenCan;
    }

    /** @var array The guarded attributes */
    protected $guarded = ['type'];

    /** @var array The hidden attributes from the model's JSON */
    protected $hidden = ['password', 'token', 'facebook_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array'
    ];

    /**
     * The validation rules on create
     *
     * @var array
     */
    protected $rules = [
        'email' => 'required|email|max:60',
        'username' => 'nullable|max:20',
        'firstname' => 'nullable|max:30',
        'lastname' => 'nullable|max:40',
        'password' => 'required|min:3',
        'type' => 'required|in:registered,moderator,admin',
        'token' => 'required|size:36',
        'points' => 'required|integer',
        'daily' => 'required|in:20,40,70',
        'class_id' => 'nullable|exists:classes,id'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Activity
     *
     * @return BelongsToMany
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class);
    }


    /**
     * BelongsTo Class_
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class);
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
     * HasMany Vote
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Passport
    |--------------------------------------------------------------------------
    */
    /**
     * Determine if user can do something according to its token
     *
     * @param string $scope the minimum scope
     *
     * @return bool
     */
    public function tokenCan($scope)
    {
        $order = ['admin', 'moderator', 'registered'];
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
}
