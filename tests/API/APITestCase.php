<?php

namespace Tests\API;

use Exception;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Bases\BaseModel;
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
    use DatabaseTransactions,
        DebugTrait,
        RESTTrait;

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
        $this->initializeREST();
    }


    /**
     * Handle response printing.
     *
     * @param Exception|Throwable $e
     *
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(Throwable $e)
    {
        $this->debug();
        parent::onNotSuccessfulTest($e);
    }
}
