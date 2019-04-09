<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Tests\OpenAPI\OpenAPITrait;
use Mathrix\Lumen\Tests\REST\RESTTrait;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Throwable;

/**
 * Class APITestCase.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class APITestCase extends BaseTestCase
{
    use DatabaseTransactions, RESTTrait, DebugTrait;

    /** @var BaseModel The test main model class */
    protected $modelClass = null;
    /** @var string The model table */
    protected $modelTable = null;
    /** @var string The test main model singular */
    protected $modelSingular = null;
    /** @var string The test main model plural (used to build request URLs) */
    protected $baseUri = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        OpenAPITrait::$SpecPath = __DIR__ . "/../docs/json-schema/index.json";
        self::bootOpenAPI();
    }


    public function setUp()
    {
        parent::setUp();
        $this->initializeREST();
    }


    public function onNotSuccessfulTest(Throwable $t)
    {
        if ($this->response !== null) {
            $this->debug();
        }

        parent::onNotSuccessfulTest($t);
    }
}
