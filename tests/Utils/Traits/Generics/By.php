<?php

namespace Tests\Utils\Traits\Generics;

use App\Models\BaseModel;

/**
 * Trait By.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Generics
 */
trait By
{
    /**
     * Generic by; expect HTTP 200.
     *
     * @param BaseModel|string $foreignModelClass
     * @param null $overrideId
     */
    public function genericBySuccess($foreignModelClass, $overrideId = null)
    {
        $this->genericBy($foreignModelClass, $overrideId);

        // Assertions
        $this->assertHttp200Success();
        $this->assertIsPaginatedResponse();
    }


    /**
     * Generic by call.
     *
     * @param BaseModel|string $foreignModelClass
     * @param null $overrideId
     */
    public function genericBy($foreignModelClass, $overrideId = null)
    {
        $this->genericUsableOrFail();

        $foreignId = $overrideId ?? $foreignModelClass::random()->id;
        $foreignSingular = str_singular($foreignModelClass::getTableName());

        $this->json("get", "/{$this->modelPlural}/by-$foreignSingular/$foreignId");
    }


    /**
     * Generic by; expect HTTP 422 Unauthorized.
     *
     * @param BaseModel|string $foreignModelClass
     */
    public function genericByUnauthorized($foreignModelClass)
    {
        $this->genericBy($foreignModelClass);
        $this->assertHttp401Unauthorized();
    }
}
