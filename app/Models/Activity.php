<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Activity
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $type
 * @property string|null $description
 * @property string|null $content
 * @property string|null $correction
 * @property string|null $options
 * @property-read Collection|Notion[] $notions
 * @property-read Collection|Module[] $modules
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity whereSlug($value)
 * @method static Builder|Activity whereType($value)
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereContent($value)
 * @method static Builder|Activity whereCorrection($value)
 * @method static Builder|Activity whereOptions($value)
 * @mixin \Eloquent
 */
class Activity extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'options' => 'array'
    ];

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