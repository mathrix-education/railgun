<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\SystemController;
use Illuminate\Http\Response;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;
use function file_get_contents;
use function resource_path;

class SystemRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = SystemController::class;

        $this->get('/', "$controller@base");
        $this->get('/system/health[/{count:[1-9]\d*}]', "$controller@health");
        $this->get('/system/sitemap', "$controller@sitemap");
        $this->get('/system/work', "$controller@work");

        $this->get('/docs', static function () {
            $docs = file_get_contents(resource_path('views/docs/index.html'));

            return new Response($docs);
        });
    }
}
