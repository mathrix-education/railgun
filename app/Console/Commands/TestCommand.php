<?php

namespace App\Console\Commands;

use App\Mails\SubscriptionRenewalSoonMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "test";


    public function handle()
    {
        $braintreeIds = explode("\n", file_get_contents(storage_path("stripe.csv")));

        $users = User::query()
            ->select(["firstname", "username", "email"])
            ->whereIn("stripe_id", $braintreeIds)
            ->get();

        $this->progressStart($users->count());
        foreach ($users as $user) {
            Mail::to($user->email)->sendNow(new SubscriptionRenewalSoonMail($user));
            $this->progressAdvance();
        }
        $this->progressFinish();
    }
}
