<?php
namespace Tests\Utils\Traits\Generics;

/**
 * Trait Search.
 * Generic index tests.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Generics
 */
trait Search
{
    /**
     * Generic search; expect HTTP 200.
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
     * Generic search call.
     *
     * @param array $data
     */
    public function genericSearch(array $data)
    {
        $this->genericUsableOrFail();

        $this->json("post", "/" . $this->modelPlural . "/search", $data);
    }

    /**
     * Generic search; expect HTTP 422 Unauthorized.
     *
     * @param array $data
     */
    public function genericSearchUnauthorized(array $data)
    {
        $this->genericSearch($data);
        $this->assertHttp401Unauthorized();
    }
}
