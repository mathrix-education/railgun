<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int $id
 * @property-read  string $name
 * @property int $order
 * @property bool $visible
 * @property int $notion_id
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class Subject extends Model
{
    use Randomizable;
}
