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

    public function getLabel(): string
    {
        return match ($this) {
            self::VODAFONE => __('Vodafone Cash'),
            self::ETISALAT => __('Etisalat Cash'),
            self::ORANGE => __('Orange Cash'),
            self::AMAN => __('Aman'),
            self::BANK_WALLET => __('Bank Wallet'),
            self::BANK_CARD => __('Bank Card'),
        };
    }

    public static function all(array $except = []): array
    {
        $cases = self::cases();

        if (empty($except)) {
            return $cases;
        }

        return array_filter($cases, fn ($case): bool => ! in_array($case, $except));
    }
}
