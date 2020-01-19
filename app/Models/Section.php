<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  int $id
 * @property-read  string $name
 * @property string $slug
 * @property int $order
 * @property bool $visible
 * @property int $training_id
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class Section extends Model
{
}
