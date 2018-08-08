<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Services\Braintree\Formatter;
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


    public function handle()
    {
        $query = "cus_234602f88ab637328518db0597330348";
        $results = \Braintree_Customer::search([
            \Braintree_CustomerSearch::id()->contains($query)
        ]);

        foreach ($results as $customer) {
            dd($customer);
        }
    }
}
