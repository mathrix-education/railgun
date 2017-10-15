<?php

namespace App\Policies;

/**
 * Class QuotePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
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