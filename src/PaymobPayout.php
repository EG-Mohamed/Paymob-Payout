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

    public function instantCashIn(
        IssuerType $issuer,
        float $amount,
        ?string $msisdn = null,
        ?string $bankCardNumber = null,
        ?BankTransactionType $bankTransactionType = null,
        ?BankCode $bankCode = null,
        ?string $fullName = null,
        ?string $nationalId = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $clientReferenceId = null,
        ?string $clientReference = null
    ): TransactionResponse {
        $this->validateInstantCashInFields($issuer, $amount, $msisdn, $bankCardNumber, $bankTransactionType, $bankCode, $fullName, $firstName, $lastName, $email);

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

        if ($bankCode) {
            $data['bank_code'] = $bankCode->value;
        }

        if ($fullName) {
            $data['full_name'] = $fullName;
        }

        if ($nationalId) {
            $data['national_id'] = $nationalId;
        }

        if ($firstName) {
            $data['first_name'] = $firstName;
        }

        if ($lastName) {
            $data['last_name'] = $lastName;
        }

        if ($email) {
            $data['email'] = $email;
        }

        if ($clientReferenceId) {
            $data['client_reference_id'] = $clientReferenceId;
        }

        if ($clientReference) {
            $data['client_reference'] = $clientReference;
        }

        $response = $this->client->makeAuthenticatedRequest('POST', 'disburse/', $data);

        return TransactionResponse::fromArray($response->json());
    }

    private function validateInstantCashInFields(
        IssuerType $issuer,
        float $amount,
        ?string $msisdn,
        ?string $bankCardNumber,
        ?BankTransactionType $bankTransactionType,
        ?BankCode $bankCode,
        ?string $fullName,
        ?string $firstName,
        ?string $lastName,
        ?string $email
    ): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        switch ($issuer) {
            case IssuerType::VODAFONE:
            case IssuerType::ETISALAT:
            case IssuerType::ORANGE:
            case IssuerType::BANK_WALLET:
                if (! $msisdn) {
                    throw new \InvalidArgumentException("MSISDN is required for {$issuer->getLabel()}");
                }
                if (! preg_match('/^01[0-2][0-9]{8}$/', $msisdn)) {
                    throw new \InvalidArgumentException('MSISDN must be 11 digits starting with 01');
                }
                break;

            case IssuerType::AMAN:
                if (! $msisdn) {
                    throw new \InvalidArgumentException('MSISDN is required for Aman transactions');
                }
                if (! preg_match('/^01[0-2][0-9]{8}$/', $msisdn)) {
                    throw new \InvalidArgumentException('MSISDN must be 11 digits starting with 01');
                }
                if (! $firstName) {
                    throw new \InvalidArgumentException('First name is required for Aman transactions');
                }
                if (! $lastName) {
                    throw new \InvalidArgumentException('Last name is required for Aman transactions');
                }
                if (! $email) {
                    throw new \InvalidArgumentException('Email is required for Aman transactions');
                }
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException('Invalid email format');
                }
                break;

            case IssuerType::BANK_CARD:
                if (! $bankCardNumber) {
                    throw new \InvalidArgumentException('Bank card number is required for bank card transactions');
                }
                if (! $bankTransactionType) {
                    throw new \InvalidArgumentException('Bank transaction type is required for bank card transactions');
                }
                if (! $bankCode) {
                    throw new \InvalidArgumentException('Bank code is required for bank card transactions');
                }
                if (! $fullName) {
                    throw new \InvalidArgumentException('Full name is required for bank card transactions');
                }
                if (! preg_match('/^[0-9]{13,19}$/', $bankCardNumber)) {
                    throw new \InvalidArgumentException('Bank card number must be 13-19 digits');
                }
                break;
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
