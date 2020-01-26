<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int   $user_id
 * @property string      $interactable_type
 * @property int         $interactable_id
 * @property string      $comprehension
 * @property int         $score
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Interaction extends Model
{
    use Randomizable;

    public const COMPREHENSIONS = ['blur', 'clear'];
}
