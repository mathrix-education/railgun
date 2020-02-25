<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Lumen\Auth\Authorizable;
use Railgun\Models\Traits\Randomizable;
use function trim;

/**
 * @property-read int             $id
 * @property string|null          $username
 * @property string               $email
 * @property string|null          $firstname
 * @property string|null          $lastname
 * @property string               $password
 * @property string[]             $scopes
 * @property string               $token
 * @property bool                 $premium_bypass
 * @property Carbon               $premium_bypass_expires_at
 * @property int|null             $grade_id
 * @property string|null          $stripe_id
 * @property string|null          $braintree_id
 * @property array                $settings
 * @property array                $metadata
 * @property-read  Carbon         $created_at
 * @property-read  Carbon         $updated_at
 * ---
 * @property-read string          $full_name
 * @property-read bool            $premium
 * @property-read string          $provider
 * ---
 * @property Grade                $grade
 * @property Collection|Comment[] $comments
 * @property Collection|Vote[]    $votes
 * ---
 * @method static Builder scope(string $scope)
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Randomizable;
    use Authorizable;
    use Authenticatable;

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

    /** @var array The virtual attributes */
    protected $appends = [
        'provider',
        'last_session',
    ];

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
}
