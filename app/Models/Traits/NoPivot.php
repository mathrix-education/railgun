<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Trait NoPivot.
 */
trait NoPivot
{
    /**
     * Remove the pivot column. Sounds like a hack, we should be careful.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        if (isset($data['pivot'])) {
            unset($data['pivot']);
        }

        return $data;
    }
}
