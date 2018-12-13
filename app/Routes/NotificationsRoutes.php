<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "notifications", "middleware" => "auth:api"], function () use ($router) {
    $router->patch("all/mark/read", "NotificationController@markAllRead");
    $router->get("all[/{page}[/{perPage}]]", "NotificationController@all");
    $router->get("unread[/{page}[/{perPage}]]", "NotificationController@unread");
    $router->get("read[/{page}[/{perPage}]]", "NotificationController@read");
    $router->patch("{notificationId}/mark/{type}", "NotificationController@mark");
});
