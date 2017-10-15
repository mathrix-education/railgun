<?php
use App\Models\User;

class UserTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromJson(User::class);

        $this->seedFromFactory(User::class, [
            'subFactory' => 'admin',
            'count' => random_int(2, 4)
        ]);
        $this->seedFromFactory(User::class, [
            'subFactory' => 'moderator',
            'count' => random_int(10, 20)
        ]);
        $this->seedFromFactory(User::class, [
            'subFactory' => 'registered',
            'count' => random_int(40, 60)
        ]);
    }
}