<?php

namespace MohamedSaid\PaymobPayout\DataTransferObjects;

readonly class TokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $expiresIn,
        public string $scope,
        public string $tokenType
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'],
            expiresIn: $data['expires_in'],
            scope: $data['scope'],
            tokenType: $data['token_type']
        );
    }
}
