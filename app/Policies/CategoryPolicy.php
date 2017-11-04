<?php
namespace App\Policies;

/**
 * Class CategoryPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class CategoryPolicy extends BasePolicy
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