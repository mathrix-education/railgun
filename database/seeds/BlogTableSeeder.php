<?php
namespace Database\seeds;

use App\Models\Category;
use App\Models\Module;
use App\Models\Post;
use App\Models\Quote;

/**
 * Class BlogTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class BlogTableSeeder extends BaseTableSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->seedFromFactory(Category::class, random_int(5, 8));
        $this->seedFromSubFactory(Module::class, "course", 40);
        $this->seedFromFactory(Post::class, random_int(10, 20));

        // Seed categories without posts
        $this->seedFromFactory(Category::class, random_int(5, 8));
        $this->seedFromFactory(Quote::class, random_int(10, 20));
    }
}
