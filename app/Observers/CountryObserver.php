<?php

namespace App\Observers;

use App\Models\Country;

/**
 * Class UserObserver.
 *
 * @author Mathrix Team
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
        // Sluggify if needed
        if (empty($country->slug) ||
            ($country->isDirty("name") && !$country->isDirty("slug"))) {
            $country->slug = str_slug($country->name);
        }

        return true;
    }
}
