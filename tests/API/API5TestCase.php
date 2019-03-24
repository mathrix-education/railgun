<?php

namespace Tests\API;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Mathrix\Lumen\Tests\Traits\PassportTrait;
use Mathrix\Lumen\Tests\Traits\RESTTrait;
use Tests\BaseTestCase;

/**
 * Class API5TestCase.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class API5TestCase extends BaseTestCase
{
    use DatabaseTransactions,
        DebugTrait,
        PassportTrait,
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
     * @param \Exception|\Throwable $e
     *
     * @throws \Throwable
     */
    protected function onNotSuccessfulTest(\Throwable $e)
    {
        $this->debug();
        parent::onNotSuccessfulTest($e);
    }


    /**
     * Generate the array signature from the model signature, considering that it exists.
     *
     * @return array
     */
    protected function getSignatureArray(): array
    {
        $signature = method_exists($this, "getSignature") ? $this->getSignature() : [];
        $keys = array_keys($signature);
        $keysPatched = array_map(function ($k) {
            return "data.[*].$k";
        }, $keys);

        return array_combine($keysPatched, array_values($signature));
    }
}
