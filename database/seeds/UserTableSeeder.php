<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Report;
use App\Models\User;

/**
 * Class UserTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class UserTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "admin",
            "count" => 5
        ]);
        User::where("type", "admin")
            ->first()
            ->update(["email" => "admin@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "moderator",
            "count" => 5
        ]);
        User::where("type", "moderator")
            ->first()
            ->update(["email" => "moderator@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "redactor",
            "count" => 5
        ]);
        User::where("type", "redactor")
            ->first()
            ->update(["email" => "redactor@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "support",
            "count" => 5
        ]);
        User::where("type", "support")
            ->first()
            ->update(["email" => "support@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registered",
            "count" => 30
        ]);
        User::where("type", "=", "registered")
            ->first()
            ->update(["email" => "registered@mathrix.fr"]);

        $this->seedFromJson([
            "modelClass" => \Laravel\Passport\Client::class,
            "table" => "oauth_clients"
        ]);


        // Simulate points
        Report::unguard();
        $this->seedFromFactory(["modelClass" => Report::class]);
        Report::reguard();
    }
}
