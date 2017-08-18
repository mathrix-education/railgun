<?php
namespace App\Policies;

/**
 * Class ClassPolicy
 * @author Mathrix Team
 * @package App\Policies
 */
class ClassPolicy extends BasePolicy
{
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
    public function delete() {
        return false;
    }
}