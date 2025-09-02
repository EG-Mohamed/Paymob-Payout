<?php

namespace MohamedSaid\PaymobPayout;

use MohamedSaid\PaymobPayout\DataTransferObjects\BudgetResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\BulkInquiryResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\TokenResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\TransactionResponse;
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;
use MohamedSaid\PaymobPayout\Enums\IssuerType;
use MohamedSaid\PaymobPayout\Http\PaymobClient;

class PaymobPayout
{
    public function __construct(
        protected PaymobClient $client
    ) {
    }

    public function generateToken(): TokenResponse
    {
        return $this->client->generateToken();
    }

    public function instantCashIn(
        IssuerType $issuer,
        float $amount,
        ?string $msisdn = null,
        ?string $bankCardNumber = null,
        ?BankTransactionType $bankTransactionType = null,
        ?string $fullName = null,
        ?string $nationalId = null
    ): TransactionResponse {
        $data = [
            'issuer' => $issuer->value,
            'amount' => $amount,
        ];

        if ($msisdn) {
            $data['msisdn'] = $msisdn;
        }

        if ($bankCardNumber) {
            $data['bank_card_number'] = $bankCardNumber;
        }

        if ($bankTransactionType) {
            $data['bank_transaction_type'] = $bankTransactionType->value;
        }

        if ($fullName) {
            $data['full_name'] = $fullName;
        }

        if ($nationalId) {
            $data['national_id'] = $nationalId;
        }

        $response = $this->client->makeAuthenticatedRequest('POST', 'disburse/', $data);

        return TransactionResponse::fromArray($response->json());
    }

    public function cancelAmanTransaction(string $transactionId): TransactionResponse
    {
        $response = $this->client->makeAuthenticatedRequest('POST', 'transaction/aman/cancel/', [
            'transaction_id' => $transactionId,
        ]);

        return TransactionResponse::fromArray($response->json());
    }

    public function bulkTransactionInquiry(
        array $transactionIdsList,
        bool $bankTransactions = false
    ): BulkInquiryResponse {
        $queryParams = http_build_query([
            'transactions_ids_list' => $transactionIdsList,
            'bank_transactions' => $bankTransactions ? 'true' : 'false',
        ]);

        $response = $this->client->makeAuthenticatedRequest('GET', 'transaction/inquire/?' . $queryParams);

        return BulkInquiryResponse::fromArray($response->json());
    }

    public function budgetInquiry(): BudgetResponse
    {
        $response = $this->client->makeAuthenticatedRequest('GET', 'budget/inquire/');

        return BudgetResponse::fromArray($response->json());
    }
}
