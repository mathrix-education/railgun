<?php
namespace Tests\Content;


use Tests\ScopeTestCase;

/**
 * Class ContentAsPublicTest
 * Test content as public (non-logged user)
 *
 * @author Mathrix Back-End Team
 * @package Tests\Content
 * @since
 * @copyright Mathrix Education SA
 */
class ContentAsPublicTest extends ScopeTestCase
{
    /**
     * GET /content/search
     *
     * @throws \Exception
     */
    public function testSearchGlobal()
    {
        $this->call("get", "/search/lorem");

        $this->assertHttp200Success();
        $data = $this->getResponseData();
        $this->assertTrue(isset($data->data->videos));
        $this->assertTrue(isset($data->data->chapters));
        $this->assertTrue(isset($data->data->notions));
    }


    /**
     * POST /content/upload
     */
    public function testUpload()
    {
        $this->call("post", "/content/upload");
        $this->assertHttp401Unauthorized();
    }


    /**
     * POST /content/upload
     */
    public function testTree()
    {
        $this->call("get", "/content/tree");
        $this->assertHttp200Success();
    }
}
