<?php
namespace App\Policies;

/**
 * Class QuotePolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class QuotePolicy extends BasePolicy
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
