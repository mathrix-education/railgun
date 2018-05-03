<?php
namespace App\Policies;

/**
 * Class LevelPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class LevelPolicy extends BasePolicy
{
    public function index()
    {
        return true;
    }
}
