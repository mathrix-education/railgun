<?php

declare(strict_types=1);

namespace Railgun\Models;

use Railgun\Models\Traits\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int           $id
 * @property string             $type
 * @property array              $questions
 * @property array              $choices
 * @property array              $solutions
 * @property array              $explanations
 * @property int                $order
 * @property bool               $visible
 * @property int                $subject_id
 * @property-read Carbon        $created_at
 * @property-read Carbon        $updated_at
 */
class Exercise extends Model
{
    use Randomizable;
}
