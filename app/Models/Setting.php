<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Randomizable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  int $id
 * @property-read  string $key
 * @property string $value
 * @property int $order
 * @property bool $visible
 */
class Setting extends Model
{
    use Randomizable;
}
