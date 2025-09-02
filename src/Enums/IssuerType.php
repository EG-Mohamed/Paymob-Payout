<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum IssuerType: string
{
    case VODAFONE = 'vodafone';
    case ETISALAT = 'etisalat';
    case ORANGE = 'orange';
    case AMAN = 'aman';
    case BANK_WALLET = 'bank_wallet';
    case BANK_CARD = 'bank_card';
}