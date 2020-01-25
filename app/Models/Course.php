<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int    $id
 * @property      string $name
 * @property      string $slug
 * @property      array  $content
 * @property      int    $order
 * @property      bool   $visible
 * @property      bool   $seo_index
 * @property      string $seo_title
 * @property      string $seo_slug
 * @property      string $seo_description
 * @property      int    $notion_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Course extends Model
{
    use Randomizable;

    protected $casts = ['content' => 'array'];
}
