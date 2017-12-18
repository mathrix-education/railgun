<?php
namespace App\Policies;

/**
 * Class LessonPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class LessonPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function index()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function get()
    {
        return true;
    }

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
