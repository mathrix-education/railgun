<?php
use App\Models\Category;

class CategoryTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> categories from factory');

        factory(Category::class, random_int(9, 15))->create();

        $this->println('<info>Seeded:</info>  categories from factory');
    }
}