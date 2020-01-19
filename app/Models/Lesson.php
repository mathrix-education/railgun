<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Mathrix\Lumen\Zero\Models\BaseModel;

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
class Lesson extends BaseModel
{
}
