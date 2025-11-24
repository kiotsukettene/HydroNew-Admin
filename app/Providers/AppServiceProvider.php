<?php

namespace App\Providers;

use App\Models\Device;
use App\Policies\DevicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

    /**
     * Register model policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Device::class, DevicePolicy::class);
    }
}
