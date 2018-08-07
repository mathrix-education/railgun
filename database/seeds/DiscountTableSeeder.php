<?php

use App\Models\Discount;
use App\Models\Product;

/**
 * Class DiscountTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class DiscountTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Exception
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Discount::class,
            "count" => random_int(20, 40)
        ]);

        $this->link(Discount::class, Product::class);
    }
}
