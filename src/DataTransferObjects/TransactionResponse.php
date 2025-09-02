<?php

namespace MohamedSaid\PaymobPayout\DataTransferObjects;

readonly class TransactionResponse
{
    public function __construct(
        public string $transactionId,
        public string $disbursementStatus,
        public string $statusDescription,
        public string $statusCode,
        public ?string $referenceNumber = null,
        public ?array $additionalData = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transaction_id'],
            disbursementStatus: $data['disbursement_status'],
            statusDescription: $data['status_description'],
            statusCode: $data['status_code'],
            referenceNumber: $data['reference_number'] ?? null,
            additionalData: array_diff_key($data, array_flip(['transaction_id', 'disbursement_status', 'status_description', 'status_code', 'reference_number']))
        );
    }
}