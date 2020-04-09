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
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Auth\Authorizable;
use Railgun\Billing\Models\StripeCustomer;
use Railgun\Jobs\CleanUserSession;
use Railgun\Models\Traits\HasDatabaseNotifications;
use Railgun\Models\Traits\Randomizable;
use Railgun\Support\LazyStdClass;
use Stripe\Subscription as StripeSubscription;
use function uuid_create;
use function uuid_is_valid;

/**
 * @property-read int                       $id
 * @property      string                    $uuid
 * @property      string|null               $username
 * @property      string                    $email
 * @property      string|null               $firstname
 * @property      string|null               $lastname
 * @property      string                    $password
 * @property      string[]                  $scopes
 * @property      bool                      $premium_bypass
 * @property      Carbon                    $premium_bypass_expires_at
 * @property      int|null                  $grade_id
 * @property      string|null               $stripe_id
 * @property      string|null               $braintree_id
 * @property      array                     $settings
 * @property      array                     $metadata
 * @property-read Carbon                    $created_at
 * @property-read Carbon                    $updated_at
 * ---
 * @property-read string                    $full_name
 * @property-read bool                      $premium If the user is premium
 * @property-read string                    $provider
 * @property-read LazyStdClass              $statistics
 * ---
 * @property-read Grade                     $grade
 * @property-read Collection|Comment[]      $comments
 * @property-read Collection|Vote[]         $votes
 * @property-read Collection|Interaction[]  $interactions
 * @property-read Collection|Session[]      $sessions
 * @property-read Collection|Subscription[] $subscriptions
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasDatabaseNotifications;
    use Randomizable;
    use RoutesNotifications;
    use StripeCustomer;

    protected $fillable   = [
        'email',
        'username',
        'firstname',
        'lastname',
        'password',
        'grade_id',
    ];
    protected $hidden     = [
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
    protected $casts      = [
        'scopes'                    => 'array',
        'premium_bypass'            => 'boolean',
        'premium_bypass_expires_at' => 'datetime',
        'settings'                  => 'array',
        'metadata'                  => 'array',
    ];
    protected $appends    = ['provider'];

    public static function boot(): void
    {
        parent::boot();

        self::creating(static function (User $user): void {
            if ($user->uuid !== null && uuid_is_valid($user->uuid)) {
                return;
            }

            $user->uuid = uuid_create();
        });
        self::updated(static function (User $user): void {
            if (!$user->wasChanged('grade_id')) {
                return;
            }

            Queue::push(new CleanUserSession($user));
        });
    }

    /**
     * @return BelongsTo<Grade>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return HasMany<Comment>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Interaction>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * @return HasMany<Session>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * @return HasMany<Vote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'customer_id', 'stripe_id');
    }

    /**
     * @return MorphToMany<Pack>
     */
    public function packs(): MorphToMany
    {
        return $this->morphedByMany(Pack::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    /**
     * @return MorphToMany<Training>
     */
    public function trainings(): MorphToMany
    {
        return $this->morphedByMany(Training::class, 'product', 'acquisitions')
            ->as(Acquisition::class);
    }

    /**
     * full_name attribute.
     */
    public function getFullNameAttribute(): string
    {
        if (!$this->firstname && !$this->lastname) {
            return $this->username;
        }

        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * @return bool If the user is premium.
     */
    public function getPremiumAttribute(): bool
    {
        if ($this->premium_bypass) {
            // Permanent premium bypass
            return true;
        }

        if ($this->premium_bypass_expires_at instanceof Carbon && $this->premium_bypass_expires_at->isFuture()) {
            // Temporary premium_bypass
            return true;
        }

        // Cache for the current request
        return Cache::store('array')->rememberForever('user.' . $this->id . '.premium', function (): bool {
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
     * @return string The login provider.
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

    /**
     * Hash the new password when setting it.
     *
     * @param string $password The new password.
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Get the user statistics.
     *
     * @noinspection PhpUnused
     */
    public function getStatisticsAttribute(): LazyStdClass
    {
        return new LazyStdClass([
            'interactions' => function () {
                return Interaction::query()
                    ->where('user_id', '=', $this->id)
                    ->count();
            },
            'watchtime'    => function () {
                return (int)Interaction::query()
                    ->where('user_id', '=', $this->id)
                    ->whereNotNull('watchtime')
                    ->sum('watchtime');
            },
        ]);
    }
}
