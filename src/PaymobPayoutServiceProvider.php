<?php

namespace MohamedSaid\PaymobPayout;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MohamedSaid\PaymobPayout\Commands\PaymobPayoutCommand;

class PaymobPayoutServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('paymob-payout')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_paymob_payout_table')
            ->hasCommand(PaymobPayoutCommand::class);
    }
}
