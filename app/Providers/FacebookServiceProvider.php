<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Facebook\Facebook;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use function config;

/**
 * Register the {@see Facebook SDK}, used for Facebook authentication.
 */
class FacebookServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Facebook::class, static function () {
            return new Facebook([
                'app_id'     => config('social.facebook.app_id'),
                'app_secret' => config('social.facebook.app_secret'),
            ]);
        });
    }

    /**
     * @return string[] The services provided by the provider.
     *
     * @codeCoverageIgnore
     */
    public function provides(): array
    {
        return [Facebook::class];
    }
}
