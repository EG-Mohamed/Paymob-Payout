<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum BankTransactionType: string
{
    case SALARY = 'salary';
    case CREDIT_CARD = 'credit_card';
    case PREPAID_CARD = 'prepaid_card';
    case CASH_TRANSFER = 'cash_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::SALARY =>__( 'Salary'),
            self::CREDIT_CARD =>__( 'Credit Card'),
            self::PREPAID_CARD =>__( 'Prepaid Card'),
            self::CASH_TRANSFER =>__( 'Cash Transfer'),
        };
    }

    public static function all(array $except = []): array
    {
        $cases = self::cases();

        if (empty($except)) {
            return $cases;
        }

        return array_filter($cases, fn($case) => !in_array($case, $except));
    }
}
