<?php

namespace App\Observers;

use App\Models\Country;

/**
 * Class UserObserver.
 *
 * @author    Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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
