<?php
namespace Tests\Generics;

/**
 * Trait Index
 * Generic index tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\GenericTests
 */
trait Index
{
    /**
     * Generic index call
     */
    public function genericIndex()
    {
        $this->genericUsableOrFail();

        $this->call('get', '/' . $this->modelPlural);
    }

    /**
     * Generic index; expect HTTP 200
     */
    public function genericIndexSuccess()
    {
        $this->genericIndex();

        // Assertions
        $this->assertHttp200Success();
    }

    /**
     * Generic index without pagination; expect HTTP 200
     */
    public function genericIndexSuccessWithoutPagination()
    {
        $this->genericIndexSuccess();
        $modelCount = ($this->modelClass)::count();
        $this->assertJsonLength($modelCount);
    }

    /**
     * Generic index with pagination; expect HTTP 200
     *
     * @param int $perPage model count per page
     */
    public function genericIndexSuccessWithPagination($perPage = 10)
    {
        $this->genericIndexSuccess();
        $this->assertJsonLength($perPage);
    }

    /**
     * Generic index; expect HTTP 422 Unauthorized
     */
    public function genericIndexUnauthorized()
    {
        $this->genericIndex();
        $this->assertHttp401Unauthorized();
    }
}