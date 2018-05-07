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
     * @param User $currentUser
     * @param Training $training
     *
     * @return bool
     */
    public function get(User $currentUser, Training $training)
    {
        $userTrainingIds = $currentUser->trainings->pluck("id")->toArray();
        
        return in_array($training->id, $userTrainingIds) || $this->atLeastAdmin($currentUser);
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
