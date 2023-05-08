<?php

namespace App\Repositories;

use App\Models\Retailer;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\InvalidDataProviderException;

class AuthRepository
{

    private $providers = ['user', 'retailer'];

    public function authenticateProvider(string $provider, array $fields): array
    {


        if (!in_array($provider, $this->providers)) {
            throw new InvalidDataProviderException('Wrong Provider');
        }

        $selectedProvider = $this->getProvider($provider);
        $model = $selectedProvider->where('email', $fields['email'])->first();

        if (!$model) {
            throw new AuthenticationException('Wrong Credentials');
        }

        if (!Hash::check($fields['password'], $model->password)) {
            throw new AuthenticationException('Wrong Password');
        }


        $token = $model->createToken($provider);
        return [
            'acess_token' => $token->accessToken,
            'expires_at' => $token->token->expires_at,
            'provider' => $provider
        ];
    }

    public function getProvider(string $provider): AuthenticatableContract
    {
        if ($provider == 'user') {
            return new User();
        } elseif ($provider == 'retailer') {
            return new Retailer();
        } else {
            throw new InvalidDataProviderException('Wrong Provider');
        }
    }
}
