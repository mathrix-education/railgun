<?php

namespace App\Providers;

use App\Events\CommentAnswerEvent;
use App\Events\NewSubscriptionEvent;
use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Events\TransactionRefundSuccessEvent;
use App\Events\TransactionSuccessEvent;
use App\Listeners\CommentAnswerListener;
use App\Listeners\NewSubscriptionListener;
use App\Listeners\PasswordForgottenListener;
use App\Listeners\PasswordResetListener;
use App\Listeners\SubscriptionChargedSuccessfullyListener;
use App\Listeners\TransactionRefundSuccessListener;
use App\Listeners\TransactionSuccessListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CommentAnswerEvent::class => [CommentAnswerListener::class],
        NewSubscriptionEvent::class => [NewSubscriptionListener::class],
        PasswordForgottenEvent::class => [PasswordForgottenListener::class],
        PasswordResetEvent::class => [PasswordResetListener::class],
        SubscriptionChargedSuccessfullyEvent::class => [SubscriptionChargedSuccessfullyListener::class],
        TransactionSuccessEvent::class => [TransactionSuccessListener::class],
        TransactionRefundSuccessEvent::class => [TransactionRefundSuccessListener::class],
    ];
}
