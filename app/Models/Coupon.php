<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Carbon\Carbon;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read int           $id
 * @property string             $type
 * @property int                $value
 * @property int                $counter
 * @property Carbon             $expires_at
 * @property-read Carbon        $created_at
 * @property-read Carbon        $updated_at
 */
class Coupon extends BaseModel
{
}
