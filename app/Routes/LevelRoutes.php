<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->get("levels[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "LevelController@index");
