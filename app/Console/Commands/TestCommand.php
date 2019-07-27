<?php

namespace App\Console\Commands;

use App\Mails\SubscriptionRenewalSoonMail;
use App\Models\Product;
use App\Models\User;
use App\Services\Braintree\Models\LineItem;
use Illuminate\Support\Facades\DB;
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
//        $li = LineItem::all();
//        $this->progressStart($li->count());
//
//        foreach ($li as $l) {
//            if ($l->transaction->totally_refunded) {
//                $l->negated = true;
//                $l->save();
//            }
//            $this->progressAdvance();
//        }
//        $this->progressFinish();


        DB::enableQueryLog();


//        dd(Product::query()->find(263)->lineItems()->get());
        /** @var User $user */
        $user = User::query()->where("id", "=", 1107)->firstOrFail();

        dd($user->products()->get()->toArray(), DB::getQueryLog());
    }
}
