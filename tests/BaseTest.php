<?php

/**
 * Class TestCase
 * Base
 */
abstract class BaseTest extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function __call($name, $arguments)
    {
        if(strpos($name, 'random') !== false) { // Because strpos may return 0
            /**
             * @var \App\Models\BaseModel $model
             */
            $model = 'App\Models\\' . str_replace('random', '', $name);

            return $model::inRandomOrder()->first();
        }
    }
}
