<?php

namespace Tests\API;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ModulesTest extends API5TestCase
{
    public function testIndex()
    {
        $this->assertRestIndexSuccess();
    }


    public function testGet()
    {
        $this->assertRestGetSuccess();
    }


    public function testPost()
    {
        $this->mockScope("moderator");
        $this->assertRestGetSuccess();
    }


    public function testPatch()
    {
        $this->mockScope("redactor");
        $this->assertRestPatchSuccess();
    }
}
