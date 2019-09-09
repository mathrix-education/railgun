<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class CountriesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $this->rest([
            'std:index' => null,
            'std:get' => null,
            'std:get:slug' => null,
            'std:post' => 'scope:countries:write',
            'std:patch' => 'scope:countries:write',
            'std:delete' => 'scope:countries:delete',
            'rel:get:grades' => null,
        ]);
    }
}
