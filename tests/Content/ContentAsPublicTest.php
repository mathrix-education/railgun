<?php
namespace Tests\Content;

use App\Models\Class_;
use App\Models\Country;
use Tests\ScopeTestCase;

/**
 * Class ContentAsPublicTest
 * Test content as public (non-logged user)
 *
 * @author Mathrix Back-End Team
 * @package Tests\Content
 * @since
 * @copyright Mathrix SA
 */
class ContentAsPublicTest extends ScopeTestCase
{
    /**
     * GET /content/countries
     */
    public function testCountries()
    {
        $this->get("/content/countries");

        $this->assertHttp200Success();
        $this->assertJsonLength(Country::all()->count());
    }


    /**
     * GET /content/modules
     */
    public function testModules()
    {
        /** @var Class_ $class_ */
        $class_ = Class_::random();

        $this->get("/content/modules/{$class_->id}");

        $this->assertHttp200Success();
    }


    /**
     * GET /content/search
     *
     * @throws \Exception
     */
    public function testSearchGlobal()
    {
        $this->call("get", "/content/search/lorem");

        $this->assertHttp200Success();
        $data = $this->getResponseData();
        $this->assertTrue(isset($data->videos));
        $this->assertTrue(isset($data->chapters));
        $this->assertTrue(isset($data->notions));
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
