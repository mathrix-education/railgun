<?php

use App\Models\Product;

/**
 * Class ProductTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 */
class ProductTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromFactory(Product::class, 300);

        $faker = \Faker\Factory::create();

        $products1 = Product::where("id", "<", 50)
            ->get();
        $products2 = Product::where([["id", ">", 51], ["id", "<", 150]])
            ->get();
        $products3 = Product::where("id", ">", 151)
            ->get();

        $this->output->writeln("<comment>Linking:</comment> products <=> products");
        $linkData = [];

        // Link products 1 => products 2
        foreach ($products1 as $product1) {
            $product2IdsToLinkWithProduct1 = $faker->randomElements(
                $products2->pluck("id")
                    ->toArray(),
                $faker->numberBetween(2, 3)
            );

            foreach ($product2IdsToLinkWithProduct1 as $product2Id) {
                $linkData[] = [
                    "parent_id" => $product1->id,
                    "child_id" => $product2Id
                ];
            }
        }

        // Link products 2 => products 3
        foreach ($products2 as $product2) {
            $product3IdsToLinkWithProduct2 = $faker->randomElements(
                $products3->pluck("id")
                    ->toArray(),
                $faker->numberBetween(2, 3)
            );

            foreach ($product3IdsToLinkWithProduct2 as $product3Id) {
                $linkData[] = [
                    "parent_id" => $product2->id,
                    "child_id" => $product3Id
                ];
            }
        }
        $this->seedFromArray($linkData, "product_product");

        $this->output->writeln("<info>Linked:</info>  products <=> products");
    }
}
