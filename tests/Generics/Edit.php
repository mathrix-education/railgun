<?php
namespace Tests\Generics;

use Closure;

/**
 * Trait Edit
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
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
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
     */
    public function genericEditSuccess(array $override = [], Closure $callback = null)
    {
        $this->genericEdit($override, $callback);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->editModelData);
    }


    /**
     * Generic edit call
     *
     * @param array $override overridden attributes
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
     */
    public function genericEdit(array $override = [], Closure $callback = null)
    {
        $this->genericUsableOrFail();

        $this->editModelId = ($this->modelClass)::inRandomOrder()->firstOrFail()->id;
        $this->editModelData = is_null($this->subFactory) ?
            factory($this->modelClass)->raw($override) :
            factory($this->modelClass, $this->subFactory)->raw($override);

        if ($callback !== null) {
            $this->editModelData = $callback($this->editModelData);
        }

        $this->call("patch", "/" . $this->modelPlural . "/" . $this->editModelId, $this->editModelData);

        if (isset($this->editModelData["link"])) {
            unset($this->editModelData["link"]);
        }
    }


    /**
     * Generic edit; expect HTTP 200 and data present in database
     *
     * @param array $override overridden attributes
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
     */
    public function genericEditUnauthorized(array $override = [], Closure $callback = null)
    {
        $this->genericEdit($override, $callback);

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase($this->modelPlural, $this->editModelData);
    }
}
