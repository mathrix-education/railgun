<?php

namespace Tests\API;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Tests\OpenAPI\OpenAPITrait;
use Mathrix\Lumen\Tests\REST\RESTTrait;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Tests\BaseTestCase;
use Throwable;

/**
 * Class API5TestCase.
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


    public function setUp()
    {
        parent::setUp();

        $this->bootOpenAPI();
        $this->initializeREST();
    }

    public function onNotSuccessfulTest(Throwable $t)
    {
        $this->debug();
        parent::onNotSuccessfulTest($t);
    }
}
