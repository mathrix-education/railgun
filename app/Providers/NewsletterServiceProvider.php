<?php

namespace App\Providers;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;
use Spatie\Newsletter\Newsletter;
use Spatie\Newsletter\NewsletterListCollection;

/**
 * Class NewsletterServiceProvider
 *
 * @author Mathrix Back-End Team
 * @package App\Providers
 * @since 0.8.1
 * @copyright Mathrix
 */
class NewsletterServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        //$this->mergeConfigFrom(__DIR__.'/../config/newsletter.php', 'newsletter'); // Original
        $this->mergeConfigFrom(config_path('newsletter.php'), 'newsletter');

        $this->publishes([
            __DIR__.'/../config/newsletter.php' => config_path('newsletter.php'),
        ]);
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
