<?php

namespace App\Policies;

/**
 * Class ActivityPolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ActivityPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function byNotion()
    {
        return true;
    }
}
