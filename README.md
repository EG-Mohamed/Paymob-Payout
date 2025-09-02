# Laravel Paymob Payout Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eg-mohamed/paymob-payout.svg?style=flat-square)](https://packagist.org/packages/eg-mohamed/paymob-payout)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/eg-mohamed/paymob-payout/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/eg-mohamed/paymob-payout/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/eg-mohamed/paymob-payout/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/eg-mohamed/paymob-payout/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/eg-mohamed/paymob-payout.svg?style=flat-square)](https://packagist.org/packages/eg-mohamed/paymob-payout)

> **⚠️ IMPORTANT DISCLAIMER**
> 
> This is **NOT an official package** from Paymob. This package was created by me to help Laravel developers 
> integrate with Paymob Payout API, as there was no available package at the time of creation.
> 
> **If Paymob releases an official Laravel package in the future, I am strongly recommend using their official package 
> instead.**
> 
> This package is provided as-is for the benefit of the developer community and is not affiliated with or endorsed by Paymob.

A comprehensive Laravel wrapper for the Paymob Payout API that provides easy-to-use methods for:

- **Token Generation & Management** - Automatic OAuth 2.0 token handling with caching
- **Instant Cash-In** - Disburse money to wallets (Vodafone, Etisalat, Orange, Aman) and bank cards
- **Transaction Management** - Cancel Aman transactions
- **Bulk Transaction Inquiry** - Query multiple transaction statuses (with rate limiting)
- **Budget Inquiry** - Check current balance
- **Comprehensive Exception Handling** - Specific exceptions for different error scenarios

## Requirements

- **PHP**: ^8.2
- **Laravel**: ^10.0 || ^11.0 || ^12.0

## Installation

You can install the package via composer:

```bash
composer require eg-mohamed/paymob-payout
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="paymob-payout-config"
```

## Configuration

Add the following environment variables to your `.env` file:

```env
PAYMOB_PAYOUT_ENVIRONMENT=staging
PAYMOB_PAYOUT_CLIENT_ID=your_client_id
PAYMOB_PAYOUT_CLIENT_SECRET=your_client_secret
PAYMOB_PAYOUT_USERNAME=your_username
PAYMOB_PAYOUT_PASSWORD=your_password
PAYMOB_PAYOUT_TIMEOUT=30
```

Set `PAYMOB_PAYOUT_ENVIRONMENT` to `production` when you're ready to go live.

## Usage

### Basic Usage

```php
use MohamedSaid\PaymobPayout\Facades\PaymobPayout;
use MohamedSaid\PaymobPayout\Enums\IssuerType;
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;

// Generate/refresh token (handled automatically)
$token = PaymobPayout::generateToken();

// Check your current budget
$budget = PaymobPayout::budgetInquiry();
echo $budget->currentBalance; // 888.25 (float)
```

### Cash-In Methods

The package provides three specialized methods for different transaction types, each with specific validation:

#### Mobile Wallets Cash-In

For Vodafone, Etisalat, Orange, and Bank Wallet transactions:

```php
use MohamedSaid\PaymobPayout\Facades\PaymobPayout;
use MohamedSaid\PaymobPayout\Enums\IssuerType;

$transaction = PaymobPayout::walletCashIn(
    issuer: IssuerType::VODAFONE,
    amount: 100.50,
    msisdn: '01234567890',
    clientReferenceId: '550e8400-e29b-41d4-a716-446655440000' // Optional UUID4
);

echo $transaction->transactionId;
echo $transaction->disbursementStatus; // 'successful', 'pending', 'failed'
echo $transaction->statusDescription;
```

#### Aman Cash-In

For Aman transactions with specific requirements:

```php
$transaction = PaymobPayout::amanCashIn(
    amount: 250.00,
    msisdn: '01234567890',
    firstName: 'Ahmed',
    lastName: 'Mohamed',
    email: 'ahmed@example.com',
    clientReference: 'AMAN-2024-001' // Optional unique reference
);

// Aman transactions return a reference number for cash pickup
echo $transaction->referenceNumber;
```

#### Bank Card Cash-In

For bank card transactions:

```php
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;
use MohamedSaid\PaymobPayout\Enums\BankCode;

$transaction = PaymobPayout::bankCardCashIn(
    amount: 500.00,
    bankCardNumber: '1234567890123456',
    bankTransactionType: BankTransactionType::CASH_TRANSFER,
    bankCode: BankCode::CIB,
    fullName: 'Ahmed Mohamed',
    clientReference: 'BANK-2024-001' // Optional unique reference
);

// Bank transactions take 2 working days to finalize
echo $transaction->disbursementStatus; // Usually 'pending' initially
```

### Field Validation

Each method includes comprehensive validation specific to the transaction type:

#### walletCashIn() Validation
- `issuer` must be VODAFONE, ETISALAT, ORANGE, or BANK_WALLET
- `amount` must be greater than 0
- `msisdn` must be 11 digits starting with 01

#### amanCashIn() Validation
- `amount` must be greater than 0
- `msisdn` must be 11 digits starting with 01
- `firstName` is required and cannot be empty
- `lastName` is required and cannot be empty
- `email` must be valid email format

#### bankCardCashIn() Validation
- `amount` must be greater than 0
- `bankCardNumber` must be 13-19 digits
- `bankTransactionType` is required (enum)
- `bankCode` is required (enum)
- `fullName` is required and cannot be empty

#### Validation Examples

```php
// Wallet validation error
try {
    $transaction = PaymobPayout::walletCashIn(
        issuer: IssuerType::AMAN, // Invalid issuer for wallet method
        amount: 100.0,
        msisdn: '01234567890'
    );
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // "Invalid issuer type for wallet cash-in. Use VODAFONE, ETISALAT, ORANGE, or BANK_WALLET"
}

// Aman validation error
try {
    $transaction = PaymobPayout::amanCashIn(
        amount: 100.0,
        msisdn: '01234567890',
        firstName: '', // Empty first name
        lastName: 'Mohamed',
        email: 'ahmed@example.com'
    );
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // "First name is required for Aman transactions"
}
```

### Transaction Management

#### Cancel Aman Transaction

```php
$cancelResult = PaymobPayout::cancelAmanTransaction('607f2a5a-1109-43d2-a12c-9327ab2dca18');

echo $cancelResult->disbursementStatus; // 'successful' if cancelled
echo $cancelResult->referenceNumber;
```

#### Bulk Transaction Inquiry

```php
$inquiry = PaymobPayout::bulkTransactionInquiry([
    '607f2a5a-1109-43d2-a12c-9327ab2dca18',
    '708f3b6b-2210-54e3-b23d-a438bc3edb29'
]);

echo $inquiry->count; // Number of transactions returned
foreach ($inquiry->results as $transaction) {
    echo $transaction['transaction_id'];
    echo $transaction['disbursement_status'];
}

// For bank transactions (sends additional bank_transactions parameter)
$bankInquiry = PaymobPayout::bulkTransactionInquiry(
    transactionIdsList: ['transaction-id-1', 'transaction-id-2'],
    bankTransactions: true
);
```

### Exception Handling

The package provides specific exceptions for different error scenarios:

```php
use MohamedSaid\PaymobPayout\Exceptions\PaymobAuthenticationException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobTransactionLimitException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobInsufficientFundsException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobInvalidAccountException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobDuplicateTransactionException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobRateLimitException;

try {
    $transaction = PaymobPayout::instantCashIn(
        issuer: IssuerType::VODAFONE,
        amount: 1000.00,
        msisdn: '01234567890'
    );
} catch (PaymobAuthenticationException $e) {
    // Handle authentication errors (403, invalid PIN, etc.)
    echo "Authentication failed: " . $e->getMessage();
    echo "Status code: " . $e->getStatusCode();
} catch (PaymobTransactionLimitException $e) {
    // Handle limit exceeded errors (583, 604, 6061, 6065)
    echo "Transaction limit exceeded: " . $e->getMessage();
} catch (PaymobInsufficientFundsException $e) {
    // Handle insufficient funds (6005)
    echo "Insufficient funds: " . $e->getMessage();
} catch (PaymobInvalidAccountException $e) {
    // Handle invalid account errors (618, 4055, etc.)
    echo "Invalid account: " . $e->getMessage();
} catch (PaymobDuplicateTransactionException $e) {
    // Handle duplicate transaction (501)
    echo "Duplicate transaction: " . $e->getMessage();
} catch (PaymobRateLimitException $e) {
    // Handle rate limiting (429)
    echo "Rate limit exceeded: " . $e->getMessage();
}
```

### Dependency Injection

You can also use dependency injection instead of the facade:

```php
use MohamedSaid\PaymobPayout\PaymobPayout;
use MohamedSaid\PaymobPayout\Enums\IssuerType;

class PaymentService
{
    public function __construct(
        private PaymobPayout $paymobPayout
    ) {}

    public function processWalletPayment(float $amount, string $phone): void
    {
        $transaction = $this->paymobPayout->walletCashIn(
            issuer: IssuerType::VODAFONE,
            amount: $amount,
            msisdn: $phone
        );
        
        // Handle the transaction result...
    }

    public function processAmanPayment(float $amount, string $phone, string $firstName, string $lastName, string $email): void
    {
        $transaction = $this->paymobPayout->amanCashIn(
            amount: $amount,
            msisdn: $phone,
            firstName: $firstName,
            lastName: $lastName,
            email: $email
        );
        
        // Handle the transaction result...
    }
}
```

### Enum Helper Methods

All enums include helpful methods for retrieving human-readable names and filtering options:

#### Get Human-Readable Names

```php
use MohamedSaid\PaymobPayout\Enums\IssuerType;
use MohamedSaid\PaymobPayout\Enums\BankCode;
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;

// Get display names
echo IssuerType::VODAFONE->getLabel(); // "Vodafone Cash"
echo BankCode::CIB->getLabel(); // "Commercial International Bank"
echo BankTransactionType::SALARY->getLabel(); // "Salary"
```

#### Get All Options (with optional exclusions)

```php
// Get all issuers
$allIssuers = IssuerType::all();

// Get all issuers except bank-related ones
$walletIssuers = IssuerType::all([
    IssuerType::BANK_WALLET,
    IssuerType::BANK_CARD
]);

// Get all bank codes except specific ones
$availableBanks = BankCode::all([
    BankCode::HSBC,
    BankCode::SCB
]);

// Get all transaction types except salary
$transactionTypes = BankTransactionType::all([
    BankTransactionType::SALARY
]);
```

## Supported Features

### Issuers
- **Vodafone Cash** - Instant wallet transactions
- **Etisalat Cash** - Instant wallet transactions  
- **Orange Cash** - Instant wallet transactions
- **Aman** - Cash pickup service with reference numbers
- **Bank Wallet** - Bank wallet transactions
- **Bank Card** - Direct bank card disbursements (2 working days)

### Bank Transaction Types
- **SALARY** - For concurrent or repeated payments
- **CREDIT_CARD** - For credit card payments  
- **PREPAID_CARD** - For prepaid cards and Meeza cards payments
- **CASH_TRANSFER** - For bank accounts, debit cards etc.

### Supported Bank Codes
- **CIB** - Commercial International Bank
- **NBE** - National Bank of Egypt
- **MISR** - Banque Misr
- **ALEX** - Bank of Alexandria
- **QNB** - QNB ALAHLI
- **HSBC** - HSBC Bank Egypt
- And 25+ other banks (see BankCode enum for complete list)

### API Rate Limits
- **Transaction Inquiry**: 5 requests per minute (POST request)
- **Budget Inquiry**: 5 requests per minute (GET request)
- **Bulk Transaction Inquiry**: 50 transactions per request (POST request)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mohamed Said](https://github.com/eg-mohamed)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

This package is provided as-is for community benefit. For official support, please contact Paymob directly.

For package-specific issues, please open an issue on [GitHub](https://github.com/eg-mohamed/paymob-payout/issues).
