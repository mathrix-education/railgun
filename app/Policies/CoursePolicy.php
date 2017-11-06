<?php
namespace App\Policies;

/**
 * Class CoursePolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
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