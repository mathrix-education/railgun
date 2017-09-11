<?php

namespace App\Policies;

/**
 * Class NotionPolicy
 * @author Mathrix Team
 * @package App\Policies
 */
class NotionPolicy extends BasePolicy
{
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