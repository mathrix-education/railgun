<?php

namespace App\Models;

use App\Exceptions\ImmutableException;
use App\Models\Traits\BraintreeCustomer;
use App\Models\Traits\PermissionsTrait;
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
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\JWT\Auth\HasJWT;

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
 * @property Carbon $premium_bypass_expires_at
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
 * ---
 * @method static Builder scope(string $scope)
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authorizable,
        Authenticatable,
        Notifiable,
        BraintreeCustomer,
        StripeCustomer,
        PermissionsTrait,
        HasJWT;

    private const STRIPE_PLANS = ["v3_monthly", "v3_annually", "v3_biannually"];
    public const BRAINTREE_PLANS = ["v4_monthly", "v4_annually", "v4_biannually"];

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
    protected $hidden = [
        "password",
        "token",
        "premium_bypass",
        "premium_bypass_expires_at",
        "stripe_id",
        "braintree_id",
        "stripe",
        "braintree"
    ];

    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "scopes" => "array",
        "settings" => "array",
        "premium_bypass" => "boolean",
        "premium_bypass_expires_at" => "date"
    ];

    /** @var array The validation rules on create */
    protected $rules = [
        "username" => "nullable|max:60",
        "firstname" => "nullable|max:60",
        "lastname" => "nullable|max:60",
        "password" => "required|min:3",
        "token" => "nullable|size:36",
        "points" => "required|integer",
        "daily" => "nullable|in:10,20,40",
        "grade_id" => "nullable|exists:grades,id",
        "premium_bypass" => "boolean",
        "premium_bypass_expires_at" => "nullable|date",
    ];

    /** @var array The relations to include by default */
    protected $with = ["grade", "purchases"];

    /** @var array The virtual attributes */
    protected $appends = ["premium", "daily_points", "last_session"];


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


    /**
     * Invalidate user cache.
     */
    public function invalidate()
    {
        DB::table("cache")
            ->where("key", "LIKE", "user.{$this->id}%")
            ->delete();
    }


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /**
     * Laravel "dynamic scope" to query users with a specific scope (permission).
     *
     * @link https://laravel.com/docs/5.8/eloquent
     *
     * @param Builder $query
     * @param string $scope
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeScope(Builder $query, string $scope)
    {
        return $query->whereRaw("JSON_SEARCH(scopes, 'one', ?) IS NOT NULL", [$scope])
            ->orWhereRaw("JSON_SEARCH(scopes, 'one', '*') IS NOT NULL");
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
     * @throws ImmutableException
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
        return $this->purchases()
            ->whereIn("product_id", $product->lineage()->pluck("id"))
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
                $scopes = "admin moderator redactor registered";
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
            default:
                $scopes = "";
                break;
        }

        return $scopes;
    }


    /**
     * Get users scopes as an array.
     *
     * @return array
     */
    public function getPassportScopesArray(): array
    {
        return explode(" ", $this->getPassportScopes());
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
     * HasMany Records.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, "customer_id", "braintree_id");
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
     */
    public function getPremiumAttribute()
    {
        return $this->premium_bypass
            || ($this->premium_bypass_expires_at !== null && $this->premium_bypass_expires_at->isFuture())
            || $this->hasBraintreeSubscription(self::BRAINTREE_PLANS)
            || $this->hasStripeSubscription(self::STRIPE_PLANS);
    }


    public function getLastSessionAttribute()
    {
        if (empty($this->settings["last_session"])) {
            return [];
        }

        $body = [];
        $lastSession = $this->settings["last_session"];

        foreach ($lastSession as $moduleId) {
            /** @var Module $module */
            $module = Module::query()->findOrFail($moduleId);
            /** @var Activity $activity */
            $activity = Activity::query()->findOrFail($module->activity_id);
            /** @var Notion $notion */
            $notion = Notion::query()->findOrFail($activity->notion_id);
            /** @var Chapter $chapter */
            $chapter = Chapter::query()->findOrFail($notion->chapter_id);
            /** @var Discipline $discipline */
            $discipline = Discipline::query()->findOrFail($chapter->discipline_id);
            /** @var Grade $grade */
            $grade = Grade::query()->findOrFail($chapter->grade_id);

            $body[] = [
                "grade_slug" => $grade->slug,
                "discipline" => $discipline,
                "chapter" => $chapter,
                "activity" => $activity,
                "module" => $module,
            ];
        }

        return $body;
    }
}
