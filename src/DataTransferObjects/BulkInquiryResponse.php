<?php

namespace MohamedSaid\PaymobPayout\DataTransferObjects;

readonly class BulkInquiryResponse
{
    public function __construct(
        public int $count,
        public ?string $next,
        public ?string $previous,
        public array $results
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            count: $data['count'],
            next: $data['next'],
            previous: $data['previous'],
            results: $data['results']
        );
    }
}