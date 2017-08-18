<?php
namespace Tests;

use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\BaseTestCase;

/**
 * Class PermissionTestCase
 * Base Class for tests with permissions
 * @package Tests
 */
abstract class PermissionTestCase extends BaseTestCase
{
    /** @var User $user */
    public $user;

    /**
     * Set the user for the test
     * @return User
     */
    abstract public function initUser(): User;

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->initUser();

        Passport::actingAs($this->user);
    }

    /**
     * Get a stranger
     * @return User
     */
    public function getStranger()
    {
        do {
            $stranger = User::inRandomOrder()->first();
        } while ($stranger->id == $this->user->id);

        return $stranger;
    }

}
