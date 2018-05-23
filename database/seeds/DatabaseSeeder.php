<?php


/**
 * Class DatabaseSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class DatabaseSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->call(ContentTableSeeder::class);
        $this->call(LevelTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(TrainingTableSeeder::class);
        $this->call(BlogTableSeeder::class);
        $this->call(CommentTableSeeder::class);
        $this->call(SubscriptionTableSeeder::class);
        $this->call(MetaTableSeeder::class);
        $this->call(LogTableSeeder::class);
    }
}
