<?php

declare(strict_types=1);

namespace App\Models;

use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read  int $id
 * @property-read  string $name
 * @property-read  string $slug
 * @property bool $buyable
 * @property int $price
 * @property int $order
 * @property bool $visible
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class Training extends BaseModel
{
}
