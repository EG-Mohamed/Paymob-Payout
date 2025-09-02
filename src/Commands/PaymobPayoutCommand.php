<?php

namespace MohamedSaid\PaymobPayout\Commands;

use Illuminate\Console\Command;

class PaymobPayoutCommand extends Command
{
    public $signature = 'paymob-payout';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
