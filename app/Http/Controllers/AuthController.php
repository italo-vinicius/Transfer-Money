<?php

namespace App\Http\Controllers;

use App\Repositories\AuthRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use PHPUnit\Framework\InvalidDataProviderException;

class AuthController extends Controller
{
    private AuthRepository $repository;

    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function postAuthenticate(Request $request, string $provider)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $fields = $request->only(['email', 'password']);
        try {
            $result = $this->repository->authenticateProvider($provider, $fields);
            return response()->json($result);
        } catch (InvalidDataProviderException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 422);
        } catch (AuthenticationException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 401);
        }
    }


}
