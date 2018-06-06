<?php
namespace App\Models\Traits;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait PurchasableTrait
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Traits
 * @since 4.0.2
 * @copyright Mathrix Education SA
 */
trait PurchasableTrait
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


    /**
     * Get purchasable type
     *
     * @return string
     */
    public function getType(): string
    {
        return str_singular(self::getTableName());
    }
}
