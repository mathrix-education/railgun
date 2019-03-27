<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ActivityPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class ActivityPolicy extends BasePolicy
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
    public function post()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function patch()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function delete()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function byNotion()
    {
        return true;
    }
}
