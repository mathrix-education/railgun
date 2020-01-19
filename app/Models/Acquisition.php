<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read int         $user_id
 * @property-read string      $product_type
 * @property-read int         $product_id
 * @property-read string|null $transaction_id
 * @property-read Carbon      $created_at
 * @property-read Carbon      $updated_at
 */
class Acquisition extends BaseModel
{
    use AsPivot;

    protected $table = 'acquisitions';
}
