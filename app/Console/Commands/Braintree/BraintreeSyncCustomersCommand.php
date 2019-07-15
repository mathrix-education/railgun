<?php


namespace App\Console\Commands\Braintree;


use App\Console\Commands\BoxStyle;
use App\Models\User;
use Braintree\Customer;
use Braintree\CustomerSearch;
use Braintree\ResourceCollection;
use Carbon\Carbon;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use Ramsey\Uuid\Uuid;

class BraintreeSyncCustomersCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "braintree:sync:customers";


    public function handle()
    {
        $iterator = Carbon::createFromDate(2018, 1, 1);

        while ($iterator->isPast()) {
            $collection = gateway()->customer()->search([
                // Limited to 10k customers per year. Thanks Julien for my 5k€!
                CustomerSearch::createdAt()->between($iterator, $iterator->clone()->addYear())
            ]);

            $this->line("Syncing customers created in {$iterator->year}");
            $this->processCollection($collection);

            $iterator = $iterator->addYear();
        }
    }


    private function processCollection(ResourceCollection $collection)
    {
        $this->progressStart($collection->maximumCount());

        /**
         * @var Customer $customer
         */
        foreach ($collection as $customer) {
            /** @var User $userWithBraintreeId */
            $userWithBraintreeId = User::query()->where("braintree_id", "=", $customer->id)->first();

            if ($userWithBraintreeId === null) {
                $userWithEmail = User::query()->where("email", "=", $customer->email)->first();

                if ($userWithEmail === null) {
                    User::unguarded(function () use ($customer) {
                        $user = new User([
                            "points" => 0,
                            "email" => $customer->email,
                            "token" => Uuid::uuid4(),
                            "braintree_id" => $customer->id
                        ]);
                        $user->scopes = [];

                        $user->save();
                    });
                }
            }

            $this->progressAdvance();
        }

        $this->progressFinish();
    }
}
