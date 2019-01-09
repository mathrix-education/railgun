<?php
namespace Database\seeds;

/**
 * Class DatabaseSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Database\seeds
 */
class DatabaseSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->call(ContentTableSeeder::class);
        $this->call(LevelTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(BlogTableSeeder::class);
        $this->call(CommentTableSeeder::class);
        $this->call(MarksTableSeeder::class);
        $this->call(LogTableSeeder::class);
        $this->call(SettingTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(DiscountTableSeeder::class);
        $this->call(ScoreTableSeeder::class);
        $this->call(FamilyPackTableSeeder::class);
        $this->call(NotificationsTableSeeder::class);
    }
}
