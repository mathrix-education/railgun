<?php
namespace App\Policies;

use App\Models\User;

class ContentPolicy extends BasePolicy
{
    public function upload(User $currentUser)
    {
        return $this->atLeastAdmin($currentUser);
    }
}