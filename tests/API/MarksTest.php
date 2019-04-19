<?php

namespace Tests\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;
use Tests\APITestCase;

/**
 * Class MarksTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class MarksTest extends APITestCase
{
    /**
     * GET /marks/activities/to-revise
     */
    public function testToRevise(): void
    {
        // We try to find a user which has to-revise marks
        /** @var User $user */
        $user = User::query()
            ->inRandomOrder()
            ->whereHas("marks", function (Builder $query) {
                $query->where([
                    ["markable_type", "=", "activity"],
                    ["status", "=", "to_revise"]
                ]);
            })
            ->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $this->json("get", "/marks/activities/to-revise");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}
