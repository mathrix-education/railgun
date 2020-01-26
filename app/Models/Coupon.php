<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read int           $id
 * @property string             $type
 * @property int                $value
 * @property int                $counter
 * @property Carbon             $expires_at
 * @property-read Carbon        $created_at
 * @property-read Carbon        $updated_at
 */
class Coupon extends Model
{
    use Randomizable;
}
