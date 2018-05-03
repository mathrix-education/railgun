<?php
namespace Tests\Generics;

/**
 * Trait Index
 * Generic index tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\GenericTests
 */
trait Index
{
    /**
     * Generic index with pagination; expect HTTP 200
     *
     * @param int $perPage model count per page
     *
     * @throws \Exception
     */
    public function genericIndexSuccessWithPagination($perPage = 10)
    {
        $this->genericIndexSuccess();
        $this->assertJsonLength($perPage);
    }


    /**
     * Generic index; expect HTTP 200
     *
     * @throws \Exception
     */
    public function genericIndexSuccess()
    {
        $this->genericIndex();

        // Assertions
        $this->assertHttp200Success();
    }


    /**
     * Generic index call
     *
     * @throws \Exception
     */
    public function genericIndex()
    {
        $this->genericUsableOrFail();

        $this->call("get", "/" . $this->modelPlural . "/");
    }


    /**
     * Generic index without pagination; expect HTTP 200
     *
     * @throws \Exception
     */
    public function genericIndexSuccessWithoutPagination()
    {
        $this->genericIndexSuccess();
        $modelCount = ($this->modelClass)::count();
        $this->assertJsonLength($modelCount);
    }


    /**
     * Generic index; expect HTTP 422 Unauthorized
     *
     * @throws \Exception
     */
    public function genericIndexUnauthorized()
    {
        $this->genericIndex();
        $this->assertHttp401Unauthorized();
    }
}
