<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class PostPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class PostPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function add()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function edit()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return false;
    }
}