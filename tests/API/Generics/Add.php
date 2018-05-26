<?php
namespace Tests\API\Generics;

use Closure;
use Illuminate\Http\Response;

/**
 * Trait Add
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @since 1.0.0
 * @package Tests\GenericTests
 */
trait Add
{
    /** @var array factorized model data */
    protected $addModelData;


    /**
     * Generic add; expect HTTP 200 and data present in database
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
     */
    public function genericAddSuccess(array $override = [], Closure $callback = null)
    {
        $this->genericAdd($override, $callback);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, $this->addModelData);
    }


    /**
     * Generic add call
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
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

        // Dynamically add id from the response
        if ($this->response instanceof Response) {
            $response = $this->response->getContent();
            $json = json_decode($response);

            if (json_last_error() === JSON_ERROR_NONE && !empty($json["id"]) && is_numeric($json["id"])) {
                $this->addModelData["id"] = (int)$json["id"];
            }
        }

        if (isset($this->addModelData["link"])) {
            unset($this->addModelData["link"]);
        }
    }


    /**
     * Generic add; expect HTTP 200 and data present in database
     *
     * @param array $override the overridden properties
     * @param \Closure|null $callback the callback on generated data
     *
     * @throws \Exception
     */
    public function genericAddUnauthorized(array $override = [], Closure $callback = null)
    {
        $this->genericAdd($override, $callback);

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase($this->modelPlural, $this->addModelData);
    }
}
