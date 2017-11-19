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
     *
     * @param array $override overridden attributes
     */
    public function genericEditSuccess(array $override = [])
    {
        $this->genericEdit($override);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->editModelData);
    }


    /**
     * Generic edit call
     *
     * @param array $override Overridden attributes
     */
    public function genericEdit(array $override = [])
    {
        $this->genericUsableOrFail();

        $this->editModelId = ($this->modelClass)::inRandomOrder()->firstOrFail()->id;
        $this->editModelData = is_null($this->subFactory) ?
            factory($this->modelClass)->raw($override) :
            factory($this->modelClass, $this->subFactory)->raw($override);

        $this->call("patch", "/" . $this->modelPlural . "/" . $this->editModelId, $this->editModelData);

        if (isset($this->editModelData["link"])) {
            unset($this->editModelData["link"]);
        }
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