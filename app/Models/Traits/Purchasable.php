<?php
namespace App\Models\Traits;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Purchasable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Traits
 * @since 4.0.2
 * @copyright Mathrix Education SA
 */
trait Purchasable
{
    /**
     * MorphMany Purchase
     *
     * @return MorphMany
     * @codeCoverageIgnore
     */
    public function purchases(): MorphMany
    {
        return $this->morphMany(Purchase::class, "purchasable");
    }
}
