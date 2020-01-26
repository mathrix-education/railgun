<?php

declare(strict_types=1);

namespace Railgun\Models;

use Railgun\Models\Traits\Randomizable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int           $id
 * @property string             $name
 * @property string             $slug
 * @property bool               $buyable
 * @property int                $price
 * @property int                $order
 * @property bool               $visible
 * @property-read Carbon        $created_at
 * @property-read Carbon        $updated_at
 */
class Pack extends Model
{
    use Randomizable;
}
