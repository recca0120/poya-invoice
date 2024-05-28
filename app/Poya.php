<?php

namespace App;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

class Poya
{
    private string $token = '';

    public function __construct(
        private readonly ClientInterface $client,
        private $baseUrl = 'https://apitest4.91app.com/member-sso/poya'
    ) {
    }

    public function setToken($token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function user(): array
    {
        $query = ['access_token' => $this->token];
        $request = new Request('GET', $this->baseUrl.'?'.http_build_query($query));
        $response = $this->client->sendRequest($request);

        $result = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if ($result['Status'] !== 'Success') {
            throw new RuntimeException($result['Message']);
        }

        $data = [];
        foreach ($result['Data'] as $key => $value) {
            $data[Str::snake($key)] = $value;
        }

        return $data;
    }
}
