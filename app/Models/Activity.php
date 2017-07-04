<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Activity
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $type
 * @property string|null $description
 * @property int|null $premium
 * @property string|null $activitiable_type
 * @property int|null $activitiable_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Notion[] $notions
 * @method static Builder|Activity whereActivitiableId($value)
 * @method static Builder|Activity whereActivitiableType($value)
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereName($value)
 * @method static Builder|Activity wherePremium($value)
 * @method static Builder|Activity whereSlug($value)
 * @method static Builder|Activity whereType($value)
 * @mixin \Eloquent
 */
class Activity extends Model
{
    /**
     * BelongsToMany Notion
     * @return BelongsToMany
     */
    public function notions()
    {
        return $this->belongsToMany('App\Models\Notion');
    }
}