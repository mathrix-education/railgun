<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class ReportPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class ReportPolicy extends BasePolicy
{
    public function index(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }


    public function get(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }


    public function add()
    {
        return true;
    }


    public function edit(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }
}
