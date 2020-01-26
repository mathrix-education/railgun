<?php

declare(strict_types=1);

namespace Railgun\Models;

use Railgun\Models\Traits\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
