<?php

namespace App\Console\Commands;

use App\Jobs\RefundSponsorJob;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
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
    use BoxStyle;

    protected $signature = "test";


    public function handle()
    {
        /** @var User $user */
        $user = User::query()->findOrFail(106817);
        $module = Module::query()->findOrFail(6664);

        Passport::actingAs($user, $user->getPassportScopesArray());
        dd($module->toArray());
    }
}
