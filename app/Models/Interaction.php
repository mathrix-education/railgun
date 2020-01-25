<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  int      $user_id
 * @property string         $interactable_type
 * @property int            $interactable_id
 * @property string         $comprehension
 * @property int            $score
 * @property-read Carbon    $created_at
 * @property-read Carbon    $updated_at
 */
class Interaction extends Model
{
    use Randomizable;
}
