<?php

namespace MohamedSaid\PaymobPayout\Http;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use MohamedSaid\PaymobPayout\DataTransferObjects\TokenResponse;
use MohamedSaid\PaymobPayout\Exceptions\PaymobAuthenticationException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobDuplicateTransactionException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobInsufficientFundsException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobInvalidAccountException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobPayoutException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobRateLimitException;
use MohamedSaid\PaymobPayout\Exceptions\PaymobTransactionLimitException;

class PaymobClient
{
    protected string $baseUrl;

    protected array $credentials;

    protected string $tokenCacheKey;

    protected int $tokenTtl;

    protected int $timeout;

    public function __construct()
    {
        $config = config('paymob-payout');
        $environment = $config['environment'];

        $this->baseUrl = $config[$environment]['base_url'];
        $this->credentials = $config['credentials'];
        $this->tokenCacheKey = $config['cache']['token_key'];
        $this->tokenTtl = $config['cache']['token_ttl'];
        $this->timeout = $config['timeout'];
    }

    public function generateToken(): TokenResponse
    {
        $cachedToken = Cache::get($this->tokenCacheKey);

        if ($cachedToken && is_array($cachedToken)) {
            return TokenResponse::fromArray($cachedToken);
        }

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl.'o/token/', [
                'client_id' => $this->credentials['client_id'],
                'client_secret' => $this->credentials['client_secret'],
                'username' => $this->credentials['username'],
                'password' => $this->credentials['password'],
            ]);

        $this->handleTokenResponse($response);

        $tokenData = $response->json();
        $tokenResponse = TokenResponse::fromArray($tokenData);

        Cache::put($this->tokenCacheKey, $tokenData, $this->tokenTtl);

        return $tokenResponse;
    }

    public function refreshToken(string $refreshToken): TokenResponse
    {
        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl.'o/token/', [
                'client_id' => $this->credentials['client_id'],
                'client_secret' => $this->credentials['client_secret'],
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        $this->handleTokenResponse($response);

        $tokenData = $response->json();
        $tokenResponse = TokenResponse::fromArray($tokenData);

        Cache::put($this->tokenCacheKey, $tokenData, $this->tokenTtl);

        return $tokenResponse;
    }

    public function makeAuthenticatedRequest(string $method, string $endpoint, array $data = []): Response
    {
        $token = $this->generateToken();

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer '.$token->accessToken,
                'Content-Type' => 'application/json',
            ])
            ->{strtolower($method)}($this->baseUrl.$endpoint, $data);

        $this->handleApiResponse($response);

        return $response;
    }

    protected function handleTokenResponse(Response $response): void
    {
        if ($response->status() === 400) {
            throw new PaymobAuthenticationException('Invalid credentials', '400');
        }

        if ($response->status() === 500) {
            throw new PaymobPayoutException('Server error', '500');
        }

        if ($response->status() === 404) {
            throw new PaymobPayoutException('Bad URL', '404');
        }

        if ($response->status() === 504) {
            throw new PaymobPayoutException('Bad gateway', '504');
        }

        if (! $response->successful()) {
            throw new PaymobPayoutException('Token generation failed', (string) $response->status());
        }
    }

    protected function handleApiResponse(Response $response): void
    {
        $statusCode = $response->json('status_code', (string) $response->status());

        match ($statusCode) {
            '403', '1056', '4056' => throw new PaymobAuthenticationException($response->json('status_description', 'Authentication failed'), $statusCode),
            '583', '604', '6061', '6065' => throw new PaymobTransactionLimitException($response->json('status_description', 'Transaction limit exceeded'), $statusCode),
            '618', '4055', '000102', '000105', '000108' => throw new PaymobInvalidAccountException($response->json('status_description', 'Invalid account'), $statusCode),
            '6005' => throw new PaymobInsufficientFundsException($response->json('status_description', 'Insufficient funds'), $statusCode),
            '501' => throw new PaymobDuplicateTransactionException($response->json('status_description', 'Duplicate transaction'), $statusCode),
            '429' => throw new PaymobRateLimitException($response->json('status_description', 'Rate limit exceeded'), $statusCode),
            default => null
        };

        if (! $response->successful() && ! in_array($statusCode, ['200', '8000', '8111', '8222', '8333'])) {
            throw new PaymobPayoutException(
                $response->json('status_description', 'API request failed'),
                $statusCode
            );
        }
    }
}
