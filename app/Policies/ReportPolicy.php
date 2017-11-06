<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class ReportPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
 */
class ReportPolicy extends BasePolicy
{
    public function index(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
    }


    public function add()
    {
        return true;
    }
}