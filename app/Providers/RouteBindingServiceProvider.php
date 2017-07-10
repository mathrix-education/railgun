<?php
namespace App\Providers;

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

        $binder->bind('country', function ($value) {
            $country = Country::whereSlugShort($value)->first();

            if ($country instanceof Country) {
                return $country;
            } else {
                response()->json([
                    'error' => 'not_found',
                    'message' => 'Country with slug "' . $value . '" cannot be found'
                ], 404)->send();
                die();
            }
        });

        $binder->bind('discipline', function ($value) {
            $discipline = Discipline::whereSlug($value)->first();

            if ($discipline instanceof Discipline) {
                return $discipline;
            } else {
                response()->json([
                    'error' => 'not_found',
                    'message' => 'Discipline with slug "' . $value . '" cannot be found'
                ], 404)->send();
                die();
            }
        });
    }
}