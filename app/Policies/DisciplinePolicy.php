<?php

namespace App\Policies;

/**
 * Class DisciplinePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class DisciplinePolicy extends BasePolicy
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