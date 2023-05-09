<?php

namespace Tests\Feature\app\Http\Controllers;

use App\Events\SendNotification;
use App\Models\Retailer;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase;

class TransactionsControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function createApplication()
    {
        return require './bootstrap/app.php';
    }

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testUserShouldNotSendWrongProvider()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'dadasad',
            'payee_id' => 'testepayee',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'users')->post(route('postTransaction'), $payload);
        $request->assertResponseStatus(422);
    }

    public function testUserShouldBeExistingOnProviderToTransfer()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'fodasenexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(404);

    }


    public function testUserShouldBeAValidUserToTransfer()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'fodasenexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(404);
    }

    public function testRetailerShouldNotTransfer()
    {
        $this->artisan('passport:install');
        $retailer = Retailer::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'fodasenexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($retailer, 'retailers')
            ->post(route('postTransaction'), $payload);
        $request->assertResponseStatus(401);
    }

    public function testUserShouldHaveMoneyToPerformSomeTransaction()
    {
        $this->artisan('passport:install');
        $userPayer = User::factory()->create();
        $userPayed = User::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => $userPayed->id,
            'amount' => 123
        ];
        $request = $this->actingAs($userPayer, 'users')
            ->post(route('postTransaction'), $payload);
        $request->assertResponseStatus(422);
    }

    public function testUserCanTransferMoney()
    {
        $this->expectsEvents(SendNotification::class);
        $this->artisan('passport:install');
        $userPayer = User::factory()->create();
        $userPayer->wallet->deposit(1000);

        $userPayed = User::factory()->create();

        $payload = [
            'provider' => 'users',
            'payee_id' => $userPayed->id,
            'amount' => 100
        ];
        $request = $this->actingAs($userPayer, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(200);

        $request->seeInDatabase('wallets', [
            'id' => $userPayer->wallet->id,
            'balance' => 900
        ]);

        $request->seeInDatabase('wallets', [
            'id' => $userPayed->wallet->id,
            'balance' => 100
        ]) ;



    }
}
