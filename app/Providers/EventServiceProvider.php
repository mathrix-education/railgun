<?php
namespace App\Providers;

use App\Events\NewSubscriptionEvent;
use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Listeners\NewSubscriptionListener;
use App\Listeners\PasswordForgottenListener;
use App\Listeners\PasswordResetListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Providers
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewSubscriptionEvent::class => [NewSubscriptionListener::class],
        PasswordForgottenEvent::class => [PasswordForgottenListener::class],
        PasswordResetEvent::class => [PasswordResetListener::class]
    ];
}
