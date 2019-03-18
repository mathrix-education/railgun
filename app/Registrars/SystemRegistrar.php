<?php

namespace App\Registrars;

use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class SystemRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SystemRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->post("system/sitemaps", SystemRegistrar::class . "@sitemaps");
    }
}
