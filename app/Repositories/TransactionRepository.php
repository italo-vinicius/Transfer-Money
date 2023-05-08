<?php

namespace App\Repositories;

use App\Exceptions\NoMoneyException;
use App\Exceptions\TransactionDeniedException;
use App\Models\Retailer;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\InvalidDataProviderException;

class TransactionRepository
{

    public function handle(array $data)
    {
        if (!$this->guardCanTransfer()) {
            throw new TransactionDeniedException('Retailer is not authorized to make transactions', 401);
        }
        $model = $this->getProvider($data['provider']);
        $user = $model->findOrfail($data['payee_id']);
        if (!$this->checkUserBalance($user, $data['amount'])) {
            throw new NoMoneyException('sem money meu mano', 422);
        }
        return [];
    }

    public function guardCanTransfer(): bool
    {
        if (Auth::guard('users')->check()) {
            return true;
        } elseif (Auth::guard('retailers')->check()) {
            return false;
        } else {
            throw new InvalidDataProviderException('Provider not found');
        }
    }

    public function getProvider(string $provider): AuthenticatableContract
    {
        if ($provider == 'users') {
            return new User();
        } elseif ($provider == 'retailers') {
            return new Retailer();
        } else {
            throw new InvalidDataProviderException('Wrong Provider', 422);
        }
    }

    private function checkUserBalance($user, $money)
    {
        try {
        return $user->wallet->balance >= $money;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }
}
