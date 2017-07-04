<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Module
 * @property int $id
 * @property string|null $type
 * @property string|null $name
 * @property string|null $question
 * @property string|null $answer
 * @property string|null $correction
 * @method static Builder|Module whereAnswer($value)
 * @method static Builder|Module whereCorrection($value)
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereName($value)
 * @method static Builder|Module whereQuestion($value)
 * @method static Builder|Module whereType($value)
 * @mixin \Eloquent
 */
class Module extends Model
{

}