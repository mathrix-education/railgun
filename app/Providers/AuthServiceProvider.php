<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Railgun\Auth\OwnershipGate;
use Railgun\Auth\PermissionGate;
use Railgun\Auth\PremiumGate;
use Railgun\Models\User;
use Railgun\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register gates
        Gate::define(PermissionGate::NAME, PermissionGate::class);
        Gate::define(PremiumGate::NAME, PremiumGate::class);
        Gate::define(OwnershipGate::NAME, OwnershipGate::class);

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
    }
}
