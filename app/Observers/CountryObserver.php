<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Country;
use Illuminate\Support\Str;

/**
 * Class CountryObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class CountryObserver extends BaseObserver
{
    /**
     * @param Country $country
     *
     * @return bool
     * @throws ForeignConstraintsException
     */
    public function deleting(Country $country)
    {
        $this->handleRelationDeletion($country, "grades");

        return true;
    }
}
