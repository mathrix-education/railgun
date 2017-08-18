<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Validator;
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
 * @property string|null $facebook_token
 * @property string $token
 * @property int $points
 * @property array $settings
 * @property int|null $class_id
 * @property-read Class_ $class_
 * @property-read Collection|\Laravel\Passport\Client[] $clients
 * @property-read Collection|\Laravel\Passport\Token[] $tokens
 * @method static Builder|User whereClassId($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFacebookToken($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePoints($value)
 * @method static Builder|User whereSettings($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUsername($value)
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

    /**
     * The attributes that are guarded.
     * @var array
     */
    protected $guarded = [
        'status',
        'rank_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'password',
        'facebook_id'
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'settings' => 'array'
    ];

    /**
     * The validation rules on create
     * @var array
     */
    protected $rules = [
        'email' => 'required|email|max:60',
        'username' => 'nullable|max:20',
        'firstname' => 'nullable|max:30',
        'lastname' => 'nullable|max:40',
        'password' => 'required|min:3',
        'status' => 'required|in:registered,premium,moderator,admin',
        'token' => 'required|size:36',
        'facebook_token' => 'nullable',
        'points' => 'required|integer'
    ];

    /**
     * @param Validator $validator
     *
     * @return Validator
     */
    public function injectInValidator($validator)
    {
        $validator->sometimes('class_id', 'required|exists:classes,id', function ($inputs) {
            return in_array($inputs->type, ['registered', 'premium']);
        });

        return $validator;
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Class_
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo('App\Models\Class_');
    }

}
