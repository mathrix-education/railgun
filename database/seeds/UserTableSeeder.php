<?php

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Log;
use App\Models\Meta;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

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
            "count" => 10
        ]);
        User::where("type", "moderator")
            ->first()
            ->update(["email" => "moderator@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registered",
            "count" => 10
        ]);
        User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->first()
            ->update(["email" => "registered@mathrix.fr"]);


        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registeredCustomer",
            "count" => 10
        ]);
        User::where("type", "registered")
            ->whereNotNull("stripe_id")
            ->first()
            ->update(["email" => "customer@mathrix.fr"]);

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
