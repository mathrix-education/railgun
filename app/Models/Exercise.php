<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

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
class Exercise
{
}
