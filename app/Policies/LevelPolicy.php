<?php
namespace App\Policies;

/**
 * Class LevelPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
 */
class LevelPolicy extends BasePolicy
{
    public function index()
    {
        return true;
    }
}