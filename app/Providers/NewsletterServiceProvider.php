<?php

namespace App\Providers;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Spatie\Newsletter\Newsletter;
use Spatie\Newsletter\NewsletterListCollection;

/**
 * Class NewsletterServiceProvider
 * Adapted for Lumen
 *
 * @see \Spatie\Newsletter\NewsletterServiceProvider
 *
 * @author Mathrix Back-End Team
 * @package App\Providers
 * @since 1.0.0
 * @copyright Mathrix SA
 *
 * @property-read Application $app
 */
class NewsletterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->configure('newsletter');
    }


    public function register()
    {
        $this->app->singleton(Newsletter::class, function () {
            $mailChimp = new Mailchimp(config('newsletter.apiKey'));

            $mailChimp->verify_ssl = config('newsletter.ssl', true);

            $configuredLists = NewsletterListCollection::createFromConfig(config('newsletter'));

            return new Newsletter($mailChimp, $configuredLists);
        });

        $this->app->alias(Newsletter::class, 'newsletter');
    }
}
