<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Discipline
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @method static Builder|Discipline whereId($value)
 * @method static Builder|Discipline whereName($value)
 * @method static Builder|Discipline whereSlug($value)
 * @mixin \Eloquent
 */
class Discipline extends Model
{

}