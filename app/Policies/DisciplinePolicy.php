<?php
namespace App\Policies;

/**
 * Class DisciplinePolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix
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