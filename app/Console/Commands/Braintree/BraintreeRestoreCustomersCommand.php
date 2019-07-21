<?php


namespace App\Console\Commands\Braintree;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BraintreeRestoreCustomersCommand extends BraintreeRestoreCommand
{
    protected $signature = "braintree:restore:customers";


    public function handle()
    {
        $this->process("customers", "users", function (Collection $collection) {
            return $collection->unique("braintree_id");
        });
    }


    /**
     * @param $record
     *
     * @return array
     * @throws Exception
     */
    protected function parse($record)
    {
        return [
            "email" => $record["Customer Email"],
            "firstname" => $this->notEmptyOrNull($record["Customer First Name"]),
            "lastname" => $this->notEmptyOrNull($record["Customer Last Name"]),
            "braintree_id" => $record["Customer ID"],
            "settings" => "{}",
            "created_at" => (new Carbon($record["Created Datetime"])),
            "updated_at" => (new Carbon($record["Last Updated Datetime"]))
        ];
    }
}
