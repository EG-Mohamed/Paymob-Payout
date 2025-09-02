<?php

namespace MohamedSaid\PaymobPayout\DataTransferObjects;

readonly class BudgetResponse
{
    public function __construct(
        public float $currentBudget,
        public ?string $statusDescription = null,
        public ?string $statusCode = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currentBudget: $data['current_balance'],
            statusDescription: $data['status_description'] ?? null,
            statusCode: $data['status_code'] ?? null
        );
    }
}
