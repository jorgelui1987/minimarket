<?php

namespace App\Providers;

use App\Services\Billing\BillingClient;
use App\Services\Billing\BillingSettings;
use App\Services\Billing\ChileBillingClient;
use App\Services\Billing\LocalBillingClient;
use App\Services\Billing\NullBillingClient;
use App\Services\Billing\RestBillingClient;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Facturación electrónica: la config sale del módulo (settings) con
        // fallback a config/env. El driver decide la implementación.
        //   local -> in-process (Greenter);  rest -> servicio HTTP;  null -> no emite.
        $this->app->bind(BillingClient::class, function () {
            if (!BillingSettings::enabled()) {
                return new NullBillingClient();
            }

            $driver = BillingSettings::driver();
            $country = BillingSettings::country();

            // Chile: gateway SII in-process con folios CAF + certificado digital.
            if ($country === 'CL' && $driver === 'local') {
                return new ChileBillingClient();
            }

            return match ($driver) {
                'local' => new LocalBillingClient(),
                'rest'  => new RestBillingClient(BillingSettings::restUrl(), BillingSettings::restToken()),
                default => new NullBillingClient(),
            };
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
