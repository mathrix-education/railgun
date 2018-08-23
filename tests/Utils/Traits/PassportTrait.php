<?php
namespace Tests\Utils\Traits;

use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Trait PassportTrait
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\Utils\Traits
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
trait PassportTrait
{
    /**
     * Set the user for the request
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        Passport::actingAs($user, [$user->type]);
    }
}
