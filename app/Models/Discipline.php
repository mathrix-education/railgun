<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Discipline
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @method static Builder|Discipline whereId($value)
 * @method static Builder|Discipline whereName($value)
 * @method static Builder|Discipline whereSlug($value)
 * @mixin \Eloquent
 */
class Discipline extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

}