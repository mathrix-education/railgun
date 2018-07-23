<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Newsletter\Newsletter;

/**
 * Class MailchimpSyncPremiums
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class MailchimpSyncPremiums extends Command
{
    protected $signature = "mailchimp:sync:premium";
    private const PREMIUM_LIST = "7078e5e448";


    public function handle()
    {
        $users = User::whereHas("subscriptions", function (HasMany $query) {
            $query->where("name", "=", "premium");
        })->get();

        $this->output->writeln("{$users->count()} premium users to sync");

        $this->output->progressStart($users->count());

        /** @var User $user */
        foreach ($users as $user) {
            $options = [
                "USERNAME" => $user->username,
                "FNAME" => $user->firstname,
                "LNAME" => $user->lastname,
                "CLASS" => $user->class_->name,
                "PREMIUMTYP" => $user->subscriptions()->first()->stripe_plan
            ];

            Newsletter::subscribeOrUpdate($user->email, $options, self::PREMIUM_LIST);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
