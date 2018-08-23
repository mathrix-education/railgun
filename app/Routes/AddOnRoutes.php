<?php

use App\Services\Braintree\Models\AddOn;
use Illuminate\Http\JsonResponse;

/* @var Laravel\Lumen\Routing\Router $router */
$router->get("add-ons", function () {
    return new JsonResponse(AddOn::all());
});
$router->get("add-ons/{addOnId}", function ($addOnId) {
    return new JsonResponse(AddOn::find($addOnId));
});
