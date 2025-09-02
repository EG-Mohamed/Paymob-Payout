<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum BankTransactionType: string
{
    case P2M = 'P2M';
    case P2BANKACC = 'P2BankAcc';
}