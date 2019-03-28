<?php

namespace App\Observers;

use App\Models\Country;

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
     */
    public function saving($country)
    {
        // Set the slug if needed
        if (empty($country->slug) ||
            ($country->isDirty("name") && !$country->isDirty("slug"))) {
            $country->slug = str_slug($country->name);
        }

        return true;
    }


    /**
     * @param Country $country
     *
     * @return bool
     * @throws \App\Exceptions\ForeignConstraintsException
     */
    public function deleting(Country $country)
    {
        $this->handleRelationDeletion($country, "grades");

        return true;
    }
}
