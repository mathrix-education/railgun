<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Services\Braintree\Cart;
use App\Services\Braintree\Formatter;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class Dry
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console\Commands
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class Dry extends Command
{
    protected $signature = "dry";


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function handle()
    {
        $customer = User::random();
        $customer->createBraintreeCustomer("fake-valid-visa-nonce");

        $cart = new Cart();

        $request = $cart->setCustomer($customer)
            ->setProducts([3, 4, 5])
            //->setDiscount("MODEMACHINE2018")
            ->getBraintreeSaleRequest();

        echo json_encode($request, JSON_PRETTY_PRINT) . "\n";

        dd(json_encode(\Braintree_Transaction::sale($request)), JSON_PRETTY_PRINT);
    }
}
