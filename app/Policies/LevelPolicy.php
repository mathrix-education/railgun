<?php
namespace App\Policies;

class LevelPolicy extends BasePolicy
{
    public function index()
    {
        return true;
    }
}