<?php

namespace Tests\API\Activities;

use Tests\API\APITestCase;

/**
 * Class ActivitiesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ActivitiesTest extends APITestCase
{
    /**
     * GET /activities
     */
    public function testIndex()
    {
        $this->mockScope("redactor");
        $this->assertRestIndexSuccess();
    }


    /**
     * GET /activities/{activityId}
     */
    public function testGet()
    {
        $this->mockScope("redactor");
        $this->assertRestGetSuccess();
    }


    /**
     * POST /activities
     */
    public function testPost()
    {
        $this->mockScope("moderator");
        $this->assertRestPostSuccess();
    }


    /**
     * PATCH /activities/{activityId}
     */
    public function testPatch()
    {
        $this->mockScope("redactor");
        $this->assertRestPatchSuccess();
    }
}
