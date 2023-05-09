<?php

namespace App\Http\Controllers\Transactions;

use App\Exceptions\IdleServiceException;
use App\Exceptions\NoMoneyException;
use App\Exceptions\TransactionDeniedException;
use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use PHPUnit\Framework\InvalidDataProviderException;

class TransactionsController extends Controller
{
    private TransactionRepository $repository;

    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function postTransaction(Request $request)
    {

        $this->validate($request, [
            'provider' => 'required|in:users,retailers',
            'payee_id' => 'required',
            'amount' => 'required|numeric'
        ]);
        $fields = $request->only(['provider', 'payee_id', 'amount']);
        try {
            $result = $this->repository->handle($fields);
            return response()->json($result);
        } catch (InvalidDataProviderException|NoMoneyException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 422);
        } catch (TransactionDeniedException|IdleServiceException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 401);
        }

    }
}
