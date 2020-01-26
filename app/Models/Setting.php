<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

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
