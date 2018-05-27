<?php
namespace Tests\Traits;

/**
 * Trait Search
 * Generic index tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @since 4.0.3
 * @package Tests\GenericTests
 */
trait Search
{
    /**
     * Generic search; expect HTTP 200
     *
     * @param array $data
     */
    public function genericSearchSuccess(array $data)
    {
        $this->genericSearch($data);

        // Assertions
        $this->assertHttp200Success();
    }


    /**
     * Generic search call
     *
     * @param array $data
     */
    public function genericSearch(array $data)
    {
        $this->genericUsableOrFail();

        $this->call("post", "/" . $this->modelPlural . "/search", $data);
    }


    /**
     * Generic search; expect HTTP 422 Unauthorized
     *
     * @param array $data
     */
    public function genericSearchUnauthorized(array $data)
    {
        $this->genericSearch($data);
        $this->assertHttp401Unauthorized();
    }
}
