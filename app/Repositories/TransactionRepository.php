<?php

namespace App\Repositories;

use App\Events\SendNotification;
use App\Exceptions\IdleServiceException;
use App\Exceptions\NoMoneyException;
use App\Exceptions\TransactionDeniedException;
use App\Models\Retailer;
use App\Models\Transactions\Transaction;
use App\Models\Transactions\Wallet;
use App\Models\User;
use App\Services\MockyService;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\InvalidDataProviderException;
use Ramsey\Uuid\Uuid;

class TransactionRepository
{

    public function handle(array $data)
    {
        if (!$this->guardCanTransfer()) {
            throw new TransactionDeniedException('Retailer is not authorized to make transactions', 401);
        }

        if (!$payee = $this->userProviderExists($data)) {
            throw new InvalidDataProviderException('User Not Found', 404);
        }

        $myWallet = Auth::guard($data['provider'])->user()->wallet;
        if (!$this->checkUserBalance($myWallet, $data['amount'])) {
            throw new NoMoneyException('sem money meu mano', 422);
        }

        if (!$this->isServiceAbleToMakeTransaction()) {
            throw new IdleServiceException('Service is not respondind. Try again later');
        }

        return $this->makeTransaction($payee, $data);
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

    private function checkUserBalance(Wallet $wallet, $money)
    {
        return $wallet->balance >= $money;
    }

    private function makeTransaction($payee, array $data)
    {
        $payload = [
            'id' => Uuid::uuid4()->toString(),
            'payer_wallet_id' => Auth::guard($data['provider'])->user()->wallet->id,
            'payee_wallet_id' => $payee->wallet->id,
            'amount' => $data['amount']
        ];
        return DB::transaction(function () use ($payload) {
            $transaction = Transaction::create($payload);
            $transaction->walletPayer->withdraw($payload['amount']);
            $transaction->walletPayee->deposit($payload['amount']);

            event(new SendNotification($transaction));

            return $transaction;

        });

    }

    /**
     * Function to know is the user exists on provider
     * both functions should trigger an expection
     * when something is wrong
     *
     * @param array $data
     */
    private function userProviderExists(array $data)
    {
        $model = $this->getProvider($data['provider']);
        return $model->findOrfail($data['payee_id']);
    }

    private function isServiceAbleToMakeTransaction(): bool
    {
        $service = app(MockyService::class)->authorizeTransaction();
        return $service['message'] = 'Autorizado';
    }
}

