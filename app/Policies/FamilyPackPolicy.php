<?php
namespace App\Policies;

use App\Models\FamilyPack;
use App\Models\User;

/**
 * Class FamilyPackPolicy
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   App\Policies
 * @since
 * @copyright Mathrix Education SA
 */
class FamilyPackPolicy
{
    /**
     * @param User       $currentUser
     * @param FamilyPack $familyPack
     *
     * @return bool
     */
    public function get(User $currentUser, FamilyPack $familyPack)
    {
        return $currentUser->isSupport() || $currentUser->id === $familyPack->parent_id;
    }

    /**
     * @param User       $currentUser
     * @param FamilyPack $familyPack
     *
     * @return bool
     */
    public function addChild(User $currentUser, FamilyPack $familyPack)
    {
        return $currentUser->isSupport() || $currentUser->id === $familyPack->parent_id;
    }

    /**
     * @param User       $currentUser
     * @param FamilyPack $familyPack
     *
     * @return bool
     */
    public function removeChild(User $currentUser, FamilyPack $familyPack)
    {
        return $currentUser->isSupport() || $currentUser->id === $familyPack->parent_id;
    }
}
