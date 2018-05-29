<?php

use App\Models\Discount;

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
    }
}
