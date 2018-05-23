<?php
namespace Tests\API\Generics;

/**
 * Trait Get
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Generics
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
trait Get
{
    /**
     * Generic get; expect HTTP 200
     *
     * @throws \Exception
     */
    public function genericGetSuccess()
    {
        $this->genericGet();

        // Assertions
        $this->assertHttp200Success();
    }


    /**
     * Generic get call
     *
     * @throws \Exception
     */
    public function genericGet()
    {
        $this->genericUsableOrFail();

        $id = $this->modelClass::random()->id;

        $this->call("get", "/" . $this->modelPlural . "/$id");
    }


    /**
     * Generic get; expect HTTP 422 Unauthorized
     *
     * @throws \Exception
     */
    public function genericGetUnauthorized()
    {
        $this->genericGet();
        $this->assertHttp401Unauthorized();
    }
}
