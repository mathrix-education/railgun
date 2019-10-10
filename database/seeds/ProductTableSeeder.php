<?php

declare(strict_types=1);

use App\Models\Module;
use App\Models\Product;
use Faker\Factory;
use Mathrix\Lumen\Zero\Database\BaseTableSeeder;

class ProductTableSeeder extends BaseTableSeeder
{
    private const PRODUCT_COUNT = 350;

    public function run(): void
    {
        $faker          = Factory::create();
        $products       = factory(Product::class, self::PRODUCT_COUNT)->raw([
            'parent_id' => null,
            'module_id' => null,
        ]);
        $productProduct = [];

        $moduleIds   = Module::query()->inRandomOrder()->get(['id'])->pluck('id');
        $range       = range(1, 50);
        $range1_50   = range(1, 50);
        $range51_150 = range(51, 150);
        shuffle($range1_50);
        shuffle($range51_150);

        foreach ($products as $key => $product) {
            $id                   = $key + 1;
            $products[$key]['id'] = $id;

            if ($id <= 50) {
                $products[$key]['type']  = 'training';
                $products[$key]['price'] = $faker->numberBetween(5, 40) * 10;
            } elseif (50 < $id && $id <= 150) {
                $products[$key]['type']      = 'section';
                $products[$key]['parent_id'] = ! empty($range1_50)
                    ? array_shift($range1_50)
                    : $faker->numberBetween(1, 50);
            } elseif (150 < $id && $id <= 300) {
                $products[$key]['type']      = 'lesson';
                $products[$key]['parent_id'] = ! empty($range51_150)
                    ? array_shift($range51_150)
                    : $faker->numberBetween(51, 150);
                $products[$key]['module_id'] = $moduleIds->shift();
            } else {
                $products[$key]['type']  = 'pack';
                $products[$key]['price'] = $faker->numberBetween(5, 40) * 10;
                $childIds                = $faker->randomElements($range, $faker->numberBetween(2, 5));

                foreach ($childIds as $order => $childId) {
                    $productProduct[] = [
                        'parent_id' => $id,
                        'child_id' => $childId,
                        'order' => $order,
                    ];
                }

                $productProduct[] = [
                    'parent_id' => $id,
                    'child_id' => self::PRODUCT_COUNT + 1,
                    'order' => count($childIds),
                ];
            }
        }

        $this->output->writeln('<comment>Seeding:</comment> products from factory');
        $this->seedFromArray($products, 'products');

        $this->output->writeln('<comment>Seeding:</comment> products_products from factory');
        $this->seedFromArray($productProduct, 'product_product');
    }
}
