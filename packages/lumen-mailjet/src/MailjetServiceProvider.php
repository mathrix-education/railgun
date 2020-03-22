<?php

declare(strict_types=1);

namespace Mathrix\Mailjet;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Mail\MailManager;
use Mailjet\MailjetSwiftMailer\SwiftMailer\MailjetTransport;
use Swift_Events_SimpleEventDispatcher;

/**
 * Register the {@see MailjetTransport} in the {@see MailManager}.
 */
class MailjetServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->app->make('mail.manager')->extend('mailjet', function () {
            /** @var Repository $config */
            $config = $this->app->make('config');

            return new MailjetTransport(
                new Swift_Events_SimpleEventDispatcher(),
                $config->get('services.mailjet.key'),
                $config->get('services.mailjet.secret'),
                $config->get('services.mailjet.call', true),
                $config->get('services.mailjet.options', []),
            );
        });
    }
}
