<?php
namespace App\Policies;

/**
 * Class PostPolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
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
