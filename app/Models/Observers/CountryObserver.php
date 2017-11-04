<?php
namespace App\Models\Observers;

use App\Models\Country;

/**
 * Class UserObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
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
        if (empty($country->name_short) ||
            ($country->isDirty("name") && !$country->isDirty("name_short"))) {
            $country->name_short = $country->name;
        }
        if (empty($country->slug_short) ||
            ($country->isDirty("name") && !$country->isDirty("slug_short"))) {
            $country->slug_short = str_slug($country->name_short);
        }

        return true;
    }
}