<?php
namespace Tests\Utils\Traits\Generics;

/**
 * Trait Get.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.1
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Generics
 */
trait Get
{
    public $getId;

    /**
     * Generic get; expect HTTP 200.
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
     * Generic get call.
     *
     * @throws \Exception
     */
    public function genericGet()
    {
        $this->genericUsableOrFail();

        $this->getId = $this->modelClass::random()->id;

        $this->json("get", "/" . $this->modelPlural . "/{$this->getId}");
    }

    /**
     * Generic get; expect HTTP 422 Unauthorized.
     *
     * @throws \Exception
     */
    public function genericGetUnauthorized()
    {
        $this->genericGet();
        $this->assertHttp401Unauthorized();
    }
}
