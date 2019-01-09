<?php
namespace App\Providers;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Spatie\Newsletter\Newsletter;
use Spatie\Newsletter\NewsletterListCollection;

/**
 * Class NewsletterServiceProvider.
 * Adapted for Lumen.
 *
 * @see                \Spatie\Newsletter\NewsletterServiceProvider
 *
 * @author             Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author             Jérémie Levain <munezero999@live.fr>
 * @since              1.0.0
 * @copyright          Mathrix Education SA
 * @package            App\Providers
 *
 * @property           Application $app
 * @codeCoverageIgnore
 */
class NewsletterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->configure("newsletter");
    }

    public function register()
    {
        $this->app->singleton(Newsletter::class, function () {
            $mailChimp = new Mailchimp(config("newsletter.apiKey"));

            $mailChimp->verify_ssl = config("newsletter.ssl", true);

            $configuredLists = NewsletterListCollection::createFromConfig(config("newsletter"));

            return new Newsletter($mailChimp, $configuredLists);
        });

        $this->app->alias(Newsletter::class, "newsletter");
    }
}
