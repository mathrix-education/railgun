<?php
namespace Tests\Generics;

/**
 * Trait Edit
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\GenericTests
 */
trait Edit
{
    /** @var int model id */
    protected $editModelId;
    /** @var array factorized model data */
    protected $editModelData;


    /**
     * Generic edit; expect HTTP 200 and data present in database
     */
    public function genericEditSuccess()
    {
        $this->genericEdit();

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->editModelData);
    }


    /**
     * Generic edit call
     */
    public function genericEdit()
    {
        $this->genericUsableOrFail();

        $this->editModelId = ($this->modelClass)::inRandomOrder()->firstOrFail()->id;
        $this->editModelData = is_null($this->subFactory) ?
            factory($this->modelClass)->raw() :
            factory($this->modelClass, $this->subFactory)->raw();

        $this->call('patch', '/' . $this->modelPlural . '/' . $this->editModelId, $this->editModelData);
    }


    /**
     * Generic edit; expect HTTP 200 and data present in database
     */
    public function genericEditUnauthorized()
    {
        $this->genericEdit();

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase($this->modelPlural, $this->editModelData);
    }
}