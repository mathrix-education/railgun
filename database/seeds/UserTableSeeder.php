<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\User;

/**
 * Class UserTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class UserTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "admin",
            "count" => random_int(2, 4)
        ]);
        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "moderator",
            "count" => random_int(10, 20)
        ]);
        $this->seedFromFactory([
            "modelClass" => User::class,
            "subFactory" => "registered",
            "count" => random_int(40, 60)
        ]);
    }
}
