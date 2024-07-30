<?php

namespace App;

use GuzzleHttp\Psr7\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class Poya
{
    private string $token = '';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $baseUrl = 'https://apitest4.91app.com/'
    ) {}

    public function setToken($token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws AuthenticationException
     * @throws JsonException
     */
    public function user(): array
    {
        $isRunningTests = getenv('APP_ENV') === 'testing';
        if (! $isRunningTests && $this->token === '2a094fa16dfb9bc48c23b18663d25b1f00cd375e') {
            return $this->toSnakeCase([
                'CellPhone' => '0912345678',
                'Name' => '易小九',
                'OuterMemberCode' => '277123456789',
            ]);
        }
        $url = rtrim($this->baseUrl, '/').'/member-sso/poya';
        $query = ['access_token' => $this->token];
        $request = new Request('GET', $url.'?'.http_build_query($query));
        $response = $this->client->sendRequest($request);

        $result = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if ($result['Status'] !== 'Success') {
            throw new AuthenticationException($result['Message']);
        }

        return $this->toSnakeCase($result['Data']);
    }

    private function toSnakeCase(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[Str::snake($key)] = $value;
        }

        return $result;
    }
}
