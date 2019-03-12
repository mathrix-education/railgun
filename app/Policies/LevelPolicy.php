<?php

namespace App\Policies;

/**
 * Class LevelPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class LevelPolicy extends BasePolicy
{
    public function index()
    {
        return true;
    }
}
