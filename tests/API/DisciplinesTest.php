<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Discipline;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

class DisciplinesTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        'std:index',
        'std:post',
        'std:get',
        'std:get:slug',
        'std:patch',
    ];

    /**
     * DELETE /disciplines/{disciplineId}
     */
    public function testDelete(): void
    {
        /** @var Discipline $discipline */
        $discipline = Discipline::random();
        $pivot      = Discipline::random(['id', '!=', $discipline->id]);

        $discipline->chapters()->update(['discipline_id' => $pivot->id]);

        $this->makeRESTJsonRequest('std:delete', null, null, [
            'conditions' => ['id', '=', $discipline->id],
        ]);
    }
}
