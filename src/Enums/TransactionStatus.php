<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum TransactionStatus: string
{
    case SUCCESSFUL = 'successful';
    case PENDING = 'pending';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}