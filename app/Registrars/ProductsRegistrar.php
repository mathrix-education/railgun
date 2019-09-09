<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ProductsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ProductsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = ProductsController::class;

        $this->rest([
            'std:index'     => 'scope:products:read',
            'std:get'       => 'scope:products:read',
            'std:get:slug'  => null,
            'std:post'      => 'scope:products:write',
            'std:patch'     => 'scope:products:write',
            'std:delete'    => 'scope:products:delete',
            'rel:get:items' => null,
        ]);

        $this->patch('products/{packId:[1-9]\d*}/items', "$controller@patchItems");
        $this->get('products/type/{type}', "$controller@paginateByType");
        $this->get('products/buyable/type/{type}', "$controller@buyable");
        $this->post('products/cart', "$controller@cart");

        $this->group(
            ['middleware' => 'logged'],
            function () use ($controller) : void {
                $this->post('products/buy', "$controller@buy");
            }
        );

        $this->get('products/{productId}/recursive', "$controller@recursive");
    }
}
