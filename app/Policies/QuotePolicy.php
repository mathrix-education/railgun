<?php
namespace App\Policies;

/**
 * Class QuotePolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
 */
class QuotePolicy extends BasePolicy
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