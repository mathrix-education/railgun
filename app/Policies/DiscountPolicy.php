<?php
namespace App\Policies;

/**
 * Class DiscountPolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class DiscountPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function index()
    {
        return false;
    }


    /**
     * @return bool
     */
    public function get()
    {
        return false;
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


    /**
     * @return bool
     */
    public function check()
    {
        return true;
    }
}
