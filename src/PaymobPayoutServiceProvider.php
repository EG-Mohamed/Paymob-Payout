<?php

namespace MohamedSaid\PaymobPayout;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MohamedSaid\PaymobPayout\Http\PaymobClient;

class PaymobPayoutServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('paymob-payout')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaymobClient::class);
        
        $this->app->bind(PaymobPayout::class, function ($app) {
            return new PaymobPayout($app->make(PaymobClient::class));
        });
    }
}
