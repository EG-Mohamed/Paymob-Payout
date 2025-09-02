<?php

namespace MohamedSaid\PaymobPayout;

use MohamedSaid\PaymobPayout\DataTransferObjects\BudgetResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\BulkInquiryResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\TokenResponse;
use MohamedSaid\PaymobPayout\DataTransferObjects\TransactionResponse;
use MohamedSaid\PaymobPayout\Enums\BankCode;
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;
use MohamedSaid\PaymobPayout\Enums\IssuerType;
use MohamedSaid\PaymobPayout\Http\PaymobClient;

class PaymobPayout
{
    public function __construct(
        protected PaymobClient $client
    ) {}

    public function generateToken(): TokenResponse
    {
        return $this->client->generateToken();
    }

    public function walletCashIn(
        IssuerType $issuer,
        float $amount,
        string $msisdn,
        ?string $clientReferenceId = null,
        ?string $clientReference = null
    ): TransactionResponse {
        if (! in_array($issuer, [IssuerType::VODAFONE, IssuerType::ETISALAT, IssuerType::ORANGE, IssuerType::BANK_WALLET])) {
            throw new \InvalidArgumentException('Invalid issuer type for wallet cash-in. Use VODAFONE, ETISALAT, ORANGE, or BANK_WALLET');
        }

        $this->validateAmount($amount);
        $this->validateMsisdn($msisdn);

        $data = [
            'issuer' => $issuer->value,
            'amount' => $amount,
            'msisdn' => $msisdn,
        ];

        if ($clientReferenceId) {
            $data['client_reference_id'] = $clientReferenceId;
        }

        if ($clientReference) {
            $data['client_reference'] = $clientReference;
        }

        $response = $this->client->makeAuthenticatedRequest('POST', 'disburse/', $data);

        return TransactionResponse::fromArray($response->json());
    }

    public function amanCashIn(
        float $amount,
        string $msisdn,
        string $firstName,
        string $lastName,
        string $email,
        ?string $clientReferenceId = null,
        ?string $clientReference = null
    ): TransactionResponse {
        $this->validateAmount($amount);
        $this->validateMsisdn($msisdn);
        $this->validateEmail($email);

        if (empty($firstName)) {
            throw new \InvalidArgumentException('First name is required for Aman transactions');
        }

        if (empty($lastName)) {
            throw new \InvalidArgumentException('Last name is required for Aman transactions');
        }

        $data = [
            'issuer' => IssuerType::AMAN->value,
            'amount' => $amount,
            'msisdn' => $msisdn,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ];

        if ($clientReferenceId) {
            $data['client_reference_id'] = $clientReferenceId;
        }

        if ($clientReference) {
            $data['client_reference'] = $clientReference;
        }

        $response = $this->client->makeAuthenticatedRequest('POST', 'disburse/', $data);

        return TransactionResponse::fromArray($response->json());
    }

    public function bankCardCashIn(
        float $amount,
        string $bankCardNumber,
        BankTransactionType $bankTransactionType,
        BankCode $bankCode,
        string $fullName,
        ?string $clientReferenceId = null,
        ?string $clientReference = null
    ): TransactionResponse {
        $this->validateAmount($amount);
        $this->validateBankCardNumber($bankCardNumber);

        if (empty($fullName)) {
            throw new \InvalidArgumentException('Full name is required for bank card transactions');
        }

        $data = [
            'issuer' => IssuerType::BANK_CARD->value,
            'amount' => $amount,
            'bank_card_number' => $bankCardNumber,
            'bank_transaction_type' => $bankTransactionType->value,
            'bank_code' => $bankCode->value,
            'full_name' => $fullName,
        ];

        if ($clientReferenceId) {
            $data['client_reference_id'] = $clientReferenceId;
        }

        if ($clientReference) {
            $data['client_reference'] = $clientReference;
        }

        $response = $this->client->makeAuthenticatedRequest('POST', 'disburse/', $data);

        return TransactionResponse::fromArray($response->json());
    }

    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }
    }

    private function validateMsisdn(string $msisdn): void
    {
        if (! preg_match('/^01[0-2][0-9]{8}$/', $msisdn)) {
            throw new \InvalidArgumentException('MSISDN must be 11 digits starting with 01');
        }
    }

    private function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    private function validateBankCardNumber(string $bankCardNumber): void
    {
        if (! preg_match('/^[0-9]{13,19}$/', $bankCardNumber)) {
            throw new \InvalidArgumentException('Bank card number must be 13-19 digits');
        }
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

        $response = $this->client->makeAuthenticatedRequest('GET', 'transaction/inquire/?'.$queryParams);

        return BulkInquiryResponse::fromArray($response->json());
    }

    public function budgetInquiry(): BudgetResponse
    {
        $response = $this->client->makeAuthenticatedRequest('GET', 'budget/inquire/');

        return BudgetResponse::fromArray($response->json());
    }
}
