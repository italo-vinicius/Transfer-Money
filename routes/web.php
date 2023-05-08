<?php

/** @var Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Models\User;
use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    $user = User::factory()->create([
        'email' => 'italo@gmail.com',
    ]);

});
$router->post('/auth/login/{provider}', ['as' => 'authenticate', 'uses' => 'AuthController@postAuthenticate']);

$router->get('/users/me', ['as' => 'usersMe', 'uses' => 'MeController@getMe']);

$router->post('/transactions', ['as' => 'postTransaction', 'uses' => '\App\Http\Controllers\Transactions\TransactionsController@postTransaction' ]);
