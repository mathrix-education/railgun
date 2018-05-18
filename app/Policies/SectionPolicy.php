<?php
namespace App\Policies;

use App\Models\Section;
use App\Models\User;

/**
 * Class SectionPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class SectionPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function index(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
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
     * @param Section $section
     *
     * @return bool
     */
    public function recursive(User $currentUser, Section $section)
    {
        return $currentUser->hasPurchased($section->id, "section") || $this->atLeastAdmin($currentUser);
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
