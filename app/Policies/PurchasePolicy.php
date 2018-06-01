<?php
namespace App\Policies;
use App\Models\User;

/**
 * Class PurchasePolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 4.0.4
 * @copyright Mathrix Education SA
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
}
