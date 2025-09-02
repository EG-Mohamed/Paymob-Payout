<?php

namespace MohamedSaid\PaymobPayout\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MohamedSaid\PaymobPayout\PaymobPayout
 */
class PaymobPayout extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MohamedSaid\PaymobPayout\PaymobPayout::class;
    }
}
