<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MockyService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://run.mocky.io/'
        ]);
    }

    public function authorizeTransaction(): array
    {
        $uri = 'v3/8fafdd68-a090-496f-8c9a-3442cf30dae6';
        try {
            $response = $this->client->request('get', $uri);
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $guzzleException) {
            return ['deu merda'];
        }
    }

    public function notifyUser(string $fakeUserId): array
    {
        $notifyClient = new Client([
            'base_uri' => 'http://o4d9z.mocklab.io'
        ]);
        $uri = '/notify';
        try {
            $response = $notifyClient->request('get', $uri);
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $guzzleException) {
            return ['message' => 'Not Authorized'];
        }
    }

}
