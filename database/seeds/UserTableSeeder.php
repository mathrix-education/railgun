<?php

use App\Models\Report;
use App\Models\User;

/**
 * Class UserTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 */
class UserTableSeeder extends BaseTableSeeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->seedFromSubFactory(User::class, "admin", 5);
        User::where("type", "admin")
            ->first()
            ->update(["email" => "admin@mathrix.fr"]);

        $this->seedFromSubFactory(User::class, "moderator", 5);
        User::where("type", "moderator")
            ->first()
            ->update(["email" => "moderator@mathrix.fr"]);

        $this->seedFromSubFactory(User::class, "redactor", 5);
        User::where("type", "redactor")
            ->first()
            ->update(["email" => "redactor@mathrix.fr"]);

        $this->seedFromSubFactory(User::class, "support", 5);
        User::where("type", "support")
            ->first()
            ->update(["email" => "support@mathrix.fr"]);

        $this->seedFromSubFactory(User::class, "registered", 30);
        User::where("type", "=", "registered")
            ->first()
            ->update(["email" => "registered@mathrix.fr"]);

        $this->seedFromJson("oauth_clients");

        // Simulate points
        $this->seedFromFactory(Report::class, 100);
    }
}
