<?php

use App\Models\Product;
use Faker\Factory;
use Mathrix\Lumen\Bases\BaseTableSeeder;

/**
 * Class ProductTableSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class ProductTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $products = factory(Product::class, 300)->raw();
        $faker = Factory::create();

        foreach ($products as $key => $product) {
            $id = $key + 1;
            $products[$key]["id"] = $id;

            if (50 < $id && $id <= 150) {
                $products[$key]["parent_id"] = $faker->numberBetween(1, 50);
            } else if (150 < $id) {
                $products[$key]["parent_id"] = $faker->numberBetween(51, 150);
            }
        }

        $this->output->writeln("<comment>Seeding:</comment> products from factory");
        $this->seedFromArray($products, "products");
        $this->output->writeln("<info>Seeded:</info>  products from factory");
    }
}
