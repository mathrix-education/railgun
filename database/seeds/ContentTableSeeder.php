<?php

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class ContentTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class ContentTableSeeder extends BaseTableSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $withOrder = function(int $order, array $data) {
            return array_merge($data, ["order" => $order]);
        };

        // Content
        $this->seedFromJson("countries");
        $this->seedFromJson("classes");
        $this->seedFromJson("disciplines");
        $this->seedFromJson("class_discipline");

        $this->seedFromFactory(Chapter::class, random_int(50, 70));
        $this->seedFromFactory(Notion::class, random_int(100, 200));
        $this->seedFromFactory(Activity::class, random_int(150, 300));
        $this->seedFromSubFactory(Module::class, "random", random_int(200, 350));

        $this->link(Chapter::class, Notion::class, 3, 5, $withOrder);
        $this->link(Notion::class, Activity::class, 3, 5, $withOrder);
        $this->link(Activity::class, Module::class, 3, 5, $withOrder);
    }
}
