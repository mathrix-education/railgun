<?php

namespace Tests\API\Disciplines;

use App\Models\Grade;
use Tests\API\APITestCase;

/**
 * Class DisciplinesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class DisciplinesTest extends APITestCase
{
    public function testByGrade()
    {
        $grade = Grade::random();

        $this->json("get", "/disciplines/by-grade/{$grade->id}");

        $this->debug();
    }
}
