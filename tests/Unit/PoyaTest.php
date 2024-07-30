<?php

namespace Tests\Unit;

use App\Poya;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

class PoyaTest extends TestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_user(): void
    {
        $client = new Client;
        $client->addResponse(new Response(200, [], json_encode([
            'Status' => 'Success',
            'Message' => '',
            'Data' => [
                'CellPhone' => '0912345678',
                'Name' => '易小九',
                'OuterMemberCode' => '277123456789',
            ],
        ], JSON_THROW_ON_ERROR)));
        $poya = new Poya($client);
        $poya->setToken('2a094fa16dfb9bc48c23b18663d25b1f00cd375e');

        self::assertEquals([
            'cell_phone' => '0912345678',
            'name' => '易小九',
            'outer_member_code' => '277123456789',
        ], $poya->user());

        $request = $client->getLastRequest();

        self::assertEquals('/member-sso/poya', $request->getUri()->getPath());
        self::assertEquals(
            'access_token=2a094fa16dfb9bc48c23b18663d25b1f00cd375e',
            $request->getUri()->getQuery()
        );
    }
}
