<?php
namespace Tests\API\Generics;

use App\Models\BaseModel;

trait By
{
    /**
     * Generic by; expect HTTP 200
     *
     * @param BaseModel|string $foreignModelClass
     */
    public function genericBySuccess($foreignModelClass)
    {
        $this->genericBy($foreignModelClass);

        // Assertions
        $this->assertHttp200Success();
    }


    /**
     * Generic by call
     *
     * @param BaseModel|string $foreignModelClass
     */
    public function genericBy($foreignModelClass)
    {
        $this->genericUsableOrFail();

        $foreignId = $foreignModelClass::random()->id;
        $foreignSingular = str_singular($foreignModelClass::getTableName());

        $this->call("get", "/{$this->modelPlural}/by-$foreignSingular/$foreignId");
    }


    /**
     * Generic by; expect HTTP 422 Unauthorized
     *
     * @param BaseModel|string $foreignModelClass
     */
    public function genericByUnauthorized($foreignModelClass)
    {
        $this->genericBy($foreignModelClass);
        $this->assertHttp401Unauthorized();
    }
}
