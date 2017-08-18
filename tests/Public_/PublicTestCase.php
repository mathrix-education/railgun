<?php
namespace Tests\Public_;

use App\Models\User;
use Tests\PermissionTestCase;

class PublicTestCase extends PermissionTestCase {
    public function initUser()
    {
        return null;
    }
}