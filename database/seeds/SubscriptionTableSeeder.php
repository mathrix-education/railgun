<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Laravel\Cashier\Subscription;

/**
 * Class SubscriptionTableSeeder
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class SubscriptionTableSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run() {
        $this->seedFromFactory([
            "modelClass" => Subscription::class,
            "table" => "subscriptions",
            "count" => 5
        ]);
        $this->seedFromFactory([
            "modelClass" => Subscription::class,
            "subFactory" => "canceled",
            "table" => "subscriptions",
            "count" => 3
        ]);
    }
}
