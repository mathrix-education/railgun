<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  int           $id
 * @property string              $name
 * @property string              $slug
 * @property int                 $duration
 * @property array               $content
 * @property int                 $order
 * @property bool                $visible
 * @property int                 $chapter_id
 * @property-read Carbon         $created_at
 * @property-read Carbon         $updated_at
 */
class Exam extends Model
{
}
