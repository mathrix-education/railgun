<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Product;

/**
 * Class ProductTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class ProductTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => Product::class,
            "count" => 50
        ]);

        $faker = Faker\Factory::create();

        $products1 = Product::where("id", "<", 6)->get();
        $products2 = Product::where([["id", ">", 6], ["id", "<", 15]])->get();
        $products3 = Product::where("id", ">", 16)->get();

        $this->println("<comment>Linking:</comment> products <=> products");
        $this->output->progressStart($products1->count() + $products2->count());
        foreach ($products1 as $product1) {
            $product1->link = [
                "children" => $faker->randomElements($products2->pluck("id")->toArray(), $faker->numberBetween(2, 3))
            ];
            $product1->save();
            $this->output->progressAdvance();
        }
        foreach ($products2 as $product2) {
            $product2->link = [
                "children" => $faker->randomElements($products3->pluck("id")->toArray(), $faker->numberBetween(2, 3))
            ];
            $product2->save();
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->println("<info>Linked:</info>  products <=> products");
    }
}
