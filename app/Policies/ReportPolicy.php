<?php
namespace App\Policies;

/**
 * Class ReportPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
 */
class ReportPolicy
{
    public function index()
    {
        return true;
    }

    public function add()
    {
        return true;
    }
}