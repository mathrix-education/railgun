<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Country;

class CountryObserver extends BaseObserver
{
    /**
     * @throws ForeignConstraintsException
     */
    public function deleting(Country $country) : bool
    {
        $this->handleRelationDeletion($country, 'grades');

        return true;
    }
}
