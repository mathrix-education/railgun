<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromJson('users', function () {
            // Hash passwords
            $users = DB::select('SELECT * FROM `users` WHERE `password` NOT LIKE \'$2y$%\'');
            foreach ($users as $user) {
                DB::update('UPDATE users SET `password` = ? WHERE `id` = ?', [
                    Hash::make($user->password),
                    $user->id
                ]);
            }
        });

        $this->seedFromFactory(User::class, 'admin', 2);
        $this->seedFromFactory(User::class, 'moderator', 7);
        $this->seedFromFactory(User::class, 'premium', 20);
        $this->seedFromFactory(User::class, 'registered', 50);

    }
}