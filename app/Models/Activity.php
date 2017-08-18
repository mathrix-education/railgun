<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Activity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property-read Collection|Module[] $modules
 * @property-read Collection|Notion[] $notions
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity whereSlug($value)
 */
class Activity extends BaseModel
{
    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'options' => 'array'
    ];

    /** @var array The validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'description' => 'required|max:255'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Module
     * @return BelongsToMany
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Module');
    }

    /**
     * BelongsToMany Notion
     * @return BelongsToMany
     */
    public function notions(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Notion');
    }
}