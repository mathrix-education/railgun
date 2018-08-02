<?php
namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class Dry
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class Dry extends Command
{
    protected $signature = "dry";
    private $braintree;


    public function __construct(\Braintree\Gateway $braintree)
    {
        parent::__construct();
        $this->braintree = $braintree;
    }


    /**
     * @throws \Braintree\Exception\NotFound
     */
    public function handle()
    {
        $user = User::whereNull("braintree_id")->first();
        dd($user->hasBraintreeSubscription("v4_annually"));
    }
}
