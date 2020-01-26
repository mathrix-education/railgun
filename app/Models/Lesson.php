<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

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
