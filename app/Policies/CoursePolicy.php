<?php
namespace App\Policies;

/**
 * Class CoursePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class CoursePolicy extends BasePolicy
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