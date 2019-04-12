<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\User;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use Braintree\Subscription;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    protected $signature = "test";


    public function handle()
    {
        $modules = Module::query()->where("content", "=", "$$");
    }


    public function handle2()
    {
        $user = User::random();
        $user->createBraintreeCustomer("fake-valid-visa-nonce");

        $result = Subscription::create([
            "planId" => "v4_monthly",
            "paymentMethodToken" => $user->getBraintreeDefaultPaymentMethodToken()
        ]);
        $result = Subscription::create([
            "planId" => "v4_monthly",
            "paymentMethodToken" => $user->getBraintreeDefaultPaymentMethodToken()
        ]);
        try {
            BraintreeErrorHandler::report($result);
        } catch (BraintreeGatewayException $e) {
            dd(json_decode($e->toJsonResponse()->getContent()));
        }
    }


    public function removeNull($array)
    {
        foreach ($array as $k => $v) {
            if (is_array($v) && $v !== []) {
                $array[$k] = $this->removeNull($v);
            } else if ($v === null || $v === "Unknown" || $v === []) {
                unset($array[$k]);
            }
        }

        return $array;
    }
}
