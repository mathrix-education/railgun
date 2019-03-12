<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class PurchasePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.4
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class PurchasePolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        return $currentUser->isAdmin();
    }


    /**
     * @return bool
     */
    public function buy()
    {
        return true;
    }
}
