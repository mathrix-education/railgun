<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Mathrix\Lumen\Models\BaseModel;

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
 * @property array $settings
 * @property int|null $class_id
 * @property-read Class_ $class_
 * @property-read Collection|Comment[] $comments
 * @property-read Collection|Vote[] $votes
 * @property-read Collection|Client[] $clients
 * @property-read Collection|Token[] $tokens
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereClassId($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFacebookToken($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereToken($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePoints($value)
 * @method static Builder|User whereSettings($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

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
        'type' => 'required|in:registered,premium,moderator,admin',
        'token' => 'required|size:36',
        'facebook_token' => 'nullable',
        'points' => 'required|integer',
        'class_id' => 'nullable|exists:classes,id'
    ];


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
     * HasMany Vote
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
