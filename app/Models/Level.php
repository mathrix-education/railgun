<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;


/**
 * App\Models\Level
 *
 * @property int $id
 * @property int $level
 * @property int $objective
 * @property string|null $youtube_id
 * @method static Builder|Level whereId($value)
 * @method static Builder|Level whereLevel($value)
 * @method static Builder|Level whereObjective($value)
 * @method static Builder|Level whereYoutubeId($value)
 * @mixin \Eloquent
 */
class Level extends BaseModel
{
    /** @var array The fillable attributes */
    public $fillable = ['level', 'objective', 'youtube_id'];

}