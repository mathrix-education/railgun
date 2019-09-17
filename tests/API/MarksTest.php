<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

class MarksTest extends APIStatelessTestCase
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
            ->whereHas('marks', static function (Builder $query) {
                $query->where([
                    ['markable_type', '=', 'activity'],
                    ['status', '=', 'to_revise'],
                ]);
            })
            ->firstOrFail();
        JWT::actingAs($user);

        $this->json('get', '/marks/activities/to-revise');

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}
