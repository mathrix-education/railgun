<?php

namespace App\Models\Traits;

/**
 * Trait NoPivot.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
trait NoPivot
{
    /**
     * Remove the pivot column. Sounds like a hack, we should be careful.
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        if (isset($data["pivot"])) {
            unset($data["pivot"]);
        }

        return $data;
    }
}
