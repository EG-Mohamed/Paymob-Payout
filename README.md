# Laravel Paymob Payout Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eg-mohamed/paymob-payout.svg?style=flat-square)](https://packagist.org/packages/eg-mohamed/paymob-payout)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/eg-mohamed/paymob-payout/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/eg-mohamed/paymob-payout/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/eg-mohamed/paymob-payout/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/eg-mohamed/paymob-payout/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/eg-mohamed/paymob-payout.svg?style=flat-square)](https://packagist.org/packages/eg-mohamed/paymob-payout)

> **⚠️ IMPORTANT DISCLAIMER**
> 
> This is **NOT an official package** from Paymob. This package was created by the community to help Laravel developers integrate with Paymob Payout API, as there was no available package at the time of creation.
> 
> **If Paymob releases an official Laravel package in the future, we strongly recommend using their official package instead.**
> 
> This package is provided as-is for the benefit of the developer community and is not affiliated with or endorsed by Paymob.

A comprehensive Laravel wrapper for the Paymob Payout API that provides easy-to-use methods for:

- **Token Generation & Management** - Automatic OAuth 2.0 token handling with caching
- **Instant Cash-In** - Disburse money to wallets (Vodafone, Etisalat, Orange, Aman) and bank cards
- **Transaction Management** - Cancel Aman transactions
- **Bulk Transaction Inquiry** - Query multiple transaction statuses (with rate limiting)
- **Budget Inquiry** - Check current balance
- **Comprehensive Exception Handling** - Specific exceptions for different error scenarios

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
echo $budget->currentBudget; // "Your current budget is 888.25 LE"
```

### Instant Cash-In Examples

#### Mobile Wallets (Vodafone, Etisalat, Orange)

```php
use MohamedSaid\PaymobPayout\Facades\PaymobPayout;
use MohamedSaid\PaymobPayout\Enums\IssuerType;

$transaction = PaymobPayout::instantCashIn(
    issuer: IssuerType::VODAFONE,
    amount: 100.50,
    msisdn: '01234567890'
);

echo $transaction->transactionId;
echo $transaction->disbursementStatus; // 'successful', 'pending', 'failed'
echo $transaction->statusDescription;
```

#### Aman Transactions

```php
$transaction = PaymobPayout::instantCashIn(
    issuer: IssuerType::AMAN,
    amount: 250.00,
    msisdn: '01234567890',
    fullName: 'Ahmed Mohamed',
    nationalId: '12345678901234'
);

// Aman transactions return a reference number
echo $transaction->referenceNumber;
```

#### Bank Card Transactions

```php
use MohamedSaid\PaymobPayout\Enums\BankTransactionType;

$transaction = PaymobPayout::instantCashIn(
    issuer: IssuerType::BANK_CARD,
    amount: 500.00,
    bankCardNumber: '1234567890123456',
    bankTransactionType: BankTransactionType::P2M
);

// Bank transactions take 2 working days to finalize
echo $transaction->disbursementStatus; // Usually 'pending' initially
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

// For bank transactions
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

class PaymentService
{
    public function __construct(
        private PaymobPayout $paymobPayout
    ) {}

    public function processPayment(float $amount, string $phone): void
    {
        $transaction = $this->paymobPayout->instantCashIn(
            issuer: IssuerType::VODAFONE,
            amount: $amount,
            msisdn: $phone
        );
        
        // Handle the transaction result...
    }
}
```

## Supported Features

### Issuers
- **Vodafone Cash** - Instant wallet transactions
- **Etisalat Cash** - Instant wallet transactions  
- **Orange Cash** - Instant wallet transactions
- **Aman** - Cash pickup service with reference numbers
- **Bank Wallet** - Bank wallet transactions
- **Bank Card** - Direct bank card disbursements (2 working days)

### Transaction Types
- **P2M** - Person to Merchant
- **P2BankAcc** - Person to Bank Account

### API Rate Limits
- **Transaction Inquiry**: 5 requests per minute
- **Budget Inquiry**: 5 requests per minute
- **Bulk Transaction Inquiry**: 50 transactions per request

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
