<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum TransactionStatus: string
{
    case SUCCESSFUL = 'successful';
    case PENDING = 'pending';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUCCESSFUL => __('Successful'),
            self::PENDING => __('Pending'),
            self::FAILED => __('Failed'),
            self::CANCELLED => __('Cancelled'),
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
