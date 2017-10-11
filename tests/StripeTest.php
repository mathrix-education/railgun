<?php
/**
 * Created by PhpStorm.
 * User: mathieu
 * Date: 11/10/17
 * Time: 15:46
 */

namespace Tests;


use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\BaseTestCase;

class StripeTest extends BaseTestCase
{
    public function testSubscribe() {
        $user = User::where('type', 'registered')->first();
        Passport::actingAs($user);

        $this->call('post', '/stripe/subscribe', [
            'stripe_token' => 'tok_1BBkZGJ3KapFDTUElng1AEyw',
            'stripe_plan' => 'v3_monthly'
        ]);

        $this->debug();
    }
}