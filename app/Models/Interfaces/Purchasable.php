<?php
namespace App\Models\Interfaces;

use App\Models\BaseModel;
use App\Models\Section;
use App\Models\Training;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface Purchasable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Interfaces
 * @since 4.0.2
 * @copyright Mathrix Education SA
 *
 * @mixin Training
 * @mixin Section
 */
interface Purchasable
{
    public function purchases(): MorphMany;

    public function getType(): string;
}
