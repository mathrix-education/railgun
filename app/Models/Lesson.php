<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  int      $id
 * @property string         $name
 * @property int            $slug
 * @property array          $content
 * @property int            $order
 * @property bool $visible
 * @property int            $section_id
 * @property-read Carbon    $created_at
 * @property-read Carbon    $updated_at
 */
class Lesson extends Model
{
    use Randomizable;
}
