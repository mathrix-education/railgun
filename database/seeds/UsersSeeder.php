<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Railgun\Models\User;

class UsersSeeder extends AdvancedSeeder
{
    private const USER_COUNT = 100;

    public function run(): void
    {
        $this->seedFactory(User::class, self::USER_COUNT);

        DB::update(/** @lang MySQL */ <<<'MySQL'
        update `users`
        set `email` = concat('admin-', `id`, '@mathrix.fr'), `scopes` = '["*"]'
        where id between 1 and 10
        MySQL,
        );
    }
}
