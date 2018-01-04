<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "comments"], function () use ($router) {
    $router->get("{id:[0-9]+}", "CommentController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("", "CommentController@index");
        $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "CommentController@index");
        $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "CommentController@latest");
        $router->post("", "CommentController@add");
        $router->patch("{id:[0-9]+}", "CommentController@edit");
        $router->delete("{id:[0-9]+}", "CommentController@delete");
        $router->post("search", "CommentController@search");
    });

    $router->get("{commentable_type}/{commentable_id:[0-9]+}", "CommentController@type");
    $router->get("{commentable_type}/{commentable_id:[0-9]+}/{page:[0-9]+}/{perPage:[0-9]+}",
        "CommentController@type");
});
