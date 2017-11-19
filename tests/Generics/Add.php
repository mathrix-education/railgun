<?php
namespace Tests\Generics;

/**
 * Trait Add
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\GenericTests
 */
trait Add
{
    /** @var array factorized model data */
    protected $addModelData;


    /**
     * Generic add; expect HTTP 200 and data present in database
     */
    public function genericAddSuccess()
    {
        $this->genericAdd();

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->addModelData);
    }


    /**
     * Generic add call
     */
    public function genericAdd()
    {
        $this->genericUsableOrFail();

        $this->addModelData = is_null($this->subFactory) ?
            factory($this->modelClass)->raw() :
            factory($this->modelClass, $this->subFactory)->raw();

        $this->call("post", "/" . $this->modelPlural, $this->addModelData);
    }


    /**
     * Generic add; expect HTTP 200 and data present in database
     */
    public function genericAddUnauthorized()
    {
        $this->genericAdd();

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase($this->modelPlural, $this->addModelData);
    }
}