<?php
namespace Tests\Generics;

/**
 * Trait Get
 *
 * @author Mathrix Back-End Team
 * @package Tests\Generics
 * @since 1.0.1
 * @copyright Mathrix SA
 */
trait Get
{
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
