<?php
namespace Tests\Generics;

use Closure;

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
     * Generic add call
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     */
    public function genericAdd(array $override = [], Closure $callback = null)
    {
        $this->genericUsableOrFail();

        $this->addModelData = is_null($this->subFactory) ?
            factory($this->modelClass)->raw($override) :
            factory($this->modelClass, $this->subFactory)->raw($override);

        if ($callback !== null) {
            $this->addModelData = $callback($this->addModelData);
        }

        $this->call("post", "/" . $this->modelPlural, $this->addModelData);
    }


    /**
     * Generic add; expect HTTP 200 and data present in database
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     */
    public function genericAddSuccess(array $override = [], Closure $callback = null)
    {
        $this->genericAdd($override, $callback);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->addModelData);
    }


    /**
     * Generic add; expect HTTP 200 and data present in database
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     */
    public function genericAddUnauthorized(array $override = [], Closure $callback = null)
    {
        $this->genericAdd($override, $callback);

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase($this->modelPlural, $this->addModelData);
    }
}