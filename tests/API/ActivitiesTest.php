<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

class ActivitiesTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:post',
        'std:get',
        'std:patch',
        'rel:get:modules',
    ];

    /**
     * DELETE /activities/{activityId}
     */
    public function testDelete(): void
    {
        $activity = Activity::random();
        $fallback = Activity::random(['id', '!=', $activity->id]);
        $activity->modules()->update(['activity_id' => $fallback->id]);

        $this->makeRESTJsonRequest('std:delete', null, null, [
            'conditions' => ['id', '=', $activity->id],
        ]);
    }

    /**
     * PATCH /activities/{activityId}/mark
     */
    public function testMark(): void
    {
        $activity = Activity::random(['visible', '=', true]);
        $uri      = "/activities/{$activity->id}/mark";
        $status   = $this->faker->randomElement(Mark::STATUSES);

        /** @var User $user */
        $user = JWT::autoScope('patch', $uri);
        $this->json('patch', $uri, ['status' => $status]);
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
        $this->assertInDatabase('marks', [
            'status' => $status,
            'user_id' => $user->id,
            'markable_type' => 'activity',
            'markable_id' => $activity->id,
        ]);
    }

    /**
     * PATCH /activities/{activityId}/unmark
     */
    public function testUnmark(): void
    {
        $mark = Mark::random(['markable_type', '=', 'activity']);
        JWT::actingAs($mark->user);

        $this->json('patch', "/activities/{$mark->markable_id}/unmark");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
        $this->assertNotInDatabase('marks', [
            'user_id' => $mark->user_id,
            'markable_type' => 'activity',
            'markable_id' => $mark->markable_id,
        ]);
    }
}
