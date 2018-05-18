<?php
namespace App\Policies;

use App\Models\Training;
use App\Models\User;

/**
 * Class TrainingPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class TrainingPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function index()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function get()
    {
        return true;
    }


    /**
     * @param User $currentUser
     * @param Training $training
     *
     * @return bool
     */
    public function recursive(User $currentUser, Training $training)
    {
        return $currentUser->hasPurchased($training->id, "training") || $this->atLeastAdmin($currentUser);
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function delete(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
    }
}
