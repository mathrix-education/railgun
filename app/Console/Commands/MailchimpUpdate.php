<?php
/**
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since ${CARET}
 * @copyright Mathrix SA
 */


namespace App\Console\Commands;


use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Newsletter\NewsletterFacade as Newsletter;

/**
 * Class MailchimpUpdate
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class MailchimpUpdate extends Command
{
    protected $signature = "mailchimp:update {--l|list= : The Mailchimp list}";


    public function handle()
    {
        $list = $this->option("list");
        $users = User::with(["class_"])->inRandomOrder()->limit(10)->get();

        $this->output->progressStart(count($users));

        foreach ($users as $user) {
            if (Newsletter::hasMember($user->email, $list)) {
                $options = [];

                if ($user->username !== null) {
                    $options["USERNAME"] = $user->username;
                } else if ($user->firstname !== null) {
                    $options["USERNAME"] = $user->firstname;
                }

                if ($user->class_ !== null) {
                    $options["CLASS"] = $user->class_->name;
                }

                if ($user->subscribed("premium")) {
                    $options["TYPE"] = "premium";
                } else {
                    $options["TYPE"] = $user->type;
                }

                Newsletter::subscribeOrUpdate($user->email, $options, $list);
            }

            $this->output->progressAdvance();
        }

        dd(Newsletter::getLastError());

        $this->output->progressFinish();
    }
}
