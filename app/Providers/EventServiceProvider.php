<?php
namespace App\Providers;

use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @author Mathrix Back-End Team
 * @package App\Providers
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PasswordForgottenEvent::class => [PasswordForgottenEvent::class],
        PasswordResetEvent::class => [PasswordResetEvent::class]
    ];
}
