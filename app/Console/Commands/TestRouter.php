<?php

namespace App\Console\Commands;

use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class TestRouter.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class TestRouter extends BaseCommand
{
    protected $signature = "router";

    public function handle()
    {
        $dispatcher = \FastRoute\simpleDispatcher(function ($r) {
            foreach (app()->router->getRoutes() as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });

        $result = $dispatcher->dispatch("GET","/users");
        dd($result);
    }
}
