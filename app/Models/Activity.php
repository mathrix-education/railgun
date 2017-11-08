<?php
namespace App\Models;

use App\Models\Traits\Loggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;


/**
 * App\Models\Activity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $name_seo
 * @property string $type
 * @property bool $visible
 * @property-read bool $favorite
 * @property-read string|null $status
 * @property-read Collection|Log[] $logs
 * @property-read Collection|Module[] $modules
 * @property-read Collection|Notion[] $notions
 * @property-read Collection|User[] $users
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity whereSlug($value)
 * @method static Builder|Activity whereNameSeo($value)
 * @method static Builder|Activity whereType($value)
 * @method static Builder|Activity whereVisible($value)
 * @mixin \Eloquent
 */
class Activity extends BaseModel
{
    use Loggable;

    public const TYPES = ["video", "course", "test", "exercise", "summary", "flashcard"];

    /** @var array The fillable attributes */
    protected $fillable = ["name", "slug", "type", "visible", "link"];

    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "options" => "array"
    ];

    /** @var array The validation rules */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "visible" => "boolean"
    ];

    /** @var array Appends attributes */
    protected $appends = ["favorite", "status"];


    public function __construct(array $attributes = [])
    {
        // Dynamic rules
        $this->rules["type"] = "required|in:" . implode(",", self::TYPES);

        parent::__construct($attributes);
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Module
     *
     * @return BelongsToMany
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }


    /**
     * BelongsToMany Notion
     *
     * @return BelongsToMany
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany(Notion::class);
    }


    /**
     * BelongsToMany Users
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Appends fields
    |--------------------------------------------------------------------------
    */
    /**
     * favorite
     * @return bool
     */
    public function getFavoriteAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $meta = Meta::where([
            ["activity_id", "=", $this->id],
            ["user_id", "=", $user->id]
        ])->first();

        return $meta === null ? false : (bool)$meta->favorite;
    }


    /**
     * status
     * @return string|null
     */
    public function getStatusAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $meta = Meta::where([
            ["activity_id", "=", $this->id],
            ["user_id", "=", $user->id]
        ])->first();

        return $meta === null ? null : $meta->status;
    }
}