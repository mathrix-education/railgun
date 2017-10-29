<?php

namespace App\Models;

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
 * @property string $description
 * @property-read Collection|Log[] $logs
 * @property-read Collection|Module[] $modules
 * @property-read Collection|Notion[] $notions
 * @property-read Collection|User[] $users
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity whereSlug($value)
 * @mixin \Eloquent
 * @property-read mixed $favorite
 * @property-read mixed $permalink
 * @property-read mixed $status
 */
class Activity extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ['name', 'slug', 'description', 'visible'];

    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        'options' => 'array'
    ];

    /** @var array The validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'description' => 'required|max:255',
        'visible' => 'boolean'
    ];

    protected $appends = ['favorite', 'status'];


    public function getFavoriteAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        return Meta::where([
            ['activity_id', '=', $this->id],
            ['user_id', '=', $user->id]
        ])->exists();
    }


    public function getStatusAttribute()
    {
        /** @var User $user */
        $user = Auth::user();

        return Meta::where([
            ['activity_id', '=', $this->id],
            ['user_id', '=', $user->id]
        ])->first()->status;
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * Get all of the post's logs.
     */
    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }


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
}