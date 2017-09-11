<?php

namespace App\Policies;

/**
 * Class ChapterPolicy
 * @author Mathrix Team
 * @package App\Policies
 */
class ChapterPolicy extends BasePolicy
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