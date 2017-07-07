<?php
namespace App\Providers;

use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

class RouteBindingServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider
     */
    public function boot()
    {
        $binder = $this->binder;

        $binder->bind('country', function($value) {
            return Country::whereSlugShort($value)->firstOrFail();
        });

        $binder->bind('discipline', function($value) {
            return Discipline::whereSlug($value)->firstOrFail();
        });
    }
}