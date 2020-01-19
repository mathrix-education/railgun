<?php

declare(strict_types=1);

namespace App\Models;

use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read  int $id
 * @property-read  string $key
 * @property string $value
 * @property int $order
 * @property bool $visible
 */
class Setting extends BaseModel
{
}
