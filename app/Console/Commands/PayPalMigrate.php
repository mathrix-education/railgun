<?php

namespace App\Console\Commands;

use App\Mails\NewPayPalMail;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PayPalSetCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-dev
 */
class PayPalMigrate extends BaseCommand
{
    use BoxStyle;

    protected $signature = "paypal:migrate";


    public function handle()
    {
        $customersIds = new Collection(json_decode(file_get_contents(storage_path("paypal.json"))));
        $customersIds->toArray();

        $customers = User::query()->whereIn("braintree_id", $customersIds->toArray());

        $this->line("Setting need_paypal_refresh");
        $this->progressStart($customers->count());
        /** @var User $customer */
        foreach ($customers as $customer) {
            $customer->settings = array_merge($customer->settings, ["need_paypal_refresh" => true]);
            $customer->save();
            $this->progressAdvance();
        }
        $this->progressFinish();


        $this->line("Sending emails");
        $this->progressStart($customers->count());
        /** @var User $customer */
        foreach ($customers as $customer) {
            Mail::to($customer->email)->send(new NewPayPalMail($customer));
            $this->progressAdvance();
        }
        $this->progressFinish();
    }
}
