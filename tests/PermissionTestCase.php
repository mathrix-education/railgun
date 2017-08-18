<?php
namespace Tests;

use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\BaseTestCase;

/**
 * Class PermissionTestCase
 * Base Class for tests with permissions
 *
 * @package Tests
 */
abstract class PermissionTestCase extends BaseTestCase
{
    /** @var User $user */
    public $user;

    /**
     * Set the user for the test
     *
     * @return User
     */
    abstract public function initUser();

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->initUser();

        if (!is_null($this->user)) {
            Passport::actingAs($this->user, [$this->user->type]);
        } else {
            unset($this->user);
        }
    }

    /**
     * Get a stranger
     *
     * @param array $where
     *
     * @return User
     */
    public function getStranger(array $where)
    {
        if (!is_array($where[0])) {
            $where = [$where];
        }
        if (isset($this->user)) {
            $where[] = ['id', '!=', $this->user->id];
        }

        /** @var User $stranger */
        $stranger = User::inRandomOrder()->where($where)->firstOrFail();

        return $stranger;
    }
}
