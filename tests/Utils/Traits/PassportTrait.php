<?php

namespace Tests\Utils\Traits;

use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Trait PassportTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits
 */
trait PassportTrait
{
    /**
     * Set the user for the request.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        Passport::actingAs($user, [$user->type]);
    }
}
