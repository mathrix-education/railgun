<?php

namespace App\Policies;

/**
 * Class LevelPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class LevelPolicy extends BasePolicy
{
    public function index()
    {
        return true;
    }
}
