<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Country;

class CountryObserver extends BaseObserver
{
    /**
     * @param Country $country
     *
     * @return bool
     * @throws ForeignConstraints
     */
    public function deleting(Country $country) : bool
    {
        $this->handleRelationDeletion($country, 'grades');

        return true;
    }
}
