<?php

namespace Laravel\Tests\Forge;

use Closure;
use Mockery;
use Laravel\Forge\Server;
use Laravel\Forge\Forge;
use PHPUnit\Framework\TestCase;
use Laravel\Tests\Forge\Helpers\Api;
use Laravel\Forge\Servers\Factory;
use Laravel\Tests\Forge\Helpers\FakeResponse;

class CreateServerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @dataProvider createServerDataProvider
     */
    public function testCreateServer(array $payload, array $response, Closure $factory)
    {
        // Servers can be created via API.

        // Create API provider.
        // Create Servers manager.
        // Create server.

        // Assert that server was created.

        $api = Api::fake(function ($http) use ($payload, $response) {
            $http->shouldReceive('request')
                ->with('POST', 'servers', ['json' => $payload])
                ->andReturn(
                    FakeResponse::fake()
                        ->withJson([
                            'server' => $response,
                            'sudo_password' => 'secret',
                            'database_password' => 'secret',
                        ])
                        ->toResponse()
                );
        });

        $forge = new Forge($api);
        $server = $factory($forge);

        $this->assertInstanceOf(Server::class, $server);
        $this->assertSame($payload['name'], $server->name());
        $this->assertSame($payload['size'], $server->size());
        $this->assertSame($payload['php_version'], $server->phpVersion());
        $this->assertSame('secret', $server->databasePassword());
        $this->assertSame('secret', $server->sudoPassword());
        $this->assertFalse($server->isReady());
    }

    /**
     * @dataProvider createServerWithDefaultCredentialDataProvider
     */
    public function testCreateServerWithDefaultCredential(int $credentialId, array $payload, array $response, Closure $factory)
    {
        $api = Api::fake(function ($http) use ($payload, $response) {
            $http->shouldReceive('request')
                ->with('POST', 'servers', ['json' => $payload])
                ->andReturn(
                    FakeResponse::fake()
                        ->withJson([
                            'server' => $response,
                            'sudo_password' => 'secret',
                            'database_password' => 'secret',
                        ])
                        ->toResponse()
                );
        });

        Factory::setDefaultCredential($payload['provider'], $credentialId);

        $forge = new Forge($api);
        $server = $factory($forge);

        Factory::resetDefaultCredential($payload['provider']);

        $this->assertInstanceOf(Server::class, $server);
        $this->assertSame($payload['name'], $server->name());
        $this->assertSame($payload['size'], $server->size());
        $this->assertSame($payload['php_version'], $server->phpVersion());
        $this->assertFalse($server->isReady());
    }

    public function payload(array $replace = [])
    {
        return array_merge([
            'credential_id' => 1,
            'database' => 'laravel',
            'node_balancer' => 1,
            'mariadb' => 1,
            'database_type' => 'mariadb',
            'name' => 'northrend',
            'network' => [1, 2, 3],
            'php_version' => 'php71',
            'provider' => 'ocean2',
            'region' => 'fra1',
            'size' => 1,
        ], $replace);
    }

    public function response(array $replace = [])
    {
        $server = Api::serverData(['is_ready' => false]);

        return array_merge($server, $replace);
    }

    public function createServerDataProvider(): array
    {
        return [
            [
                'payload' => $this->payload(),
                'response' => $this->response(),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId(1)
                        ->usingCredential(1)
                        ->at('fra1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload(
                    [
                        'size' => 19
                    ]
                ),
                'response' => $this->response(
                    [
                        'size' => 19
                    ]
                ),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId(19)
                        ->usingCredential(1)
                        ->at('fra1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload(
                    [
                        'size' => 2
                    ]
                ),
                'response' => $this->response(
                    [
                        'size' => 2
                    ]
                ),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId("02")
                        ->usingCredential(1)
                        ->at('fra1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload([
                    'mariadb' => 1
                ]),
                'response' => $this->response(),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId(1)
                        ->usingCredential(1)
                        ->at('fra1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload([
                    'php_version' => 'php72',
                ]),
                'response' => $this->response([
                    'php_version' => 'php72',
                ]),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId(1)
                        ->usingCredential(1)
                        ->at('fra1')
                        ->runningPhp('7.2')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload([
                    'provider' => 'linode',
                    'size' => 2,
                    'region' => 10,
                ]),
                'response' => $this->response([
                    'size' => 2,
                    'region' => 'Frankfurt',
                ]),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->linode('northrend')
                        ->withSizeId(2)
                        ->usingCredential(1)
                        ->at(10)
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload([
                    'provider' => 'aws',
                    'size' => 2,
                    'region' => 'us-west-1',
                ]),
                'response' => $this->response([
                    'size' => 2,
                    'region' => 'us-west-1'
                ]),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->aws('northrend')
                        ->withSizeId(2)
                        ->usingCredential(1)
                        ->at('us-west-1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => $this->payload([
                    'provider' => 'aws',
                    'size' => 3,
                    'region' => 'us-west-1',
                    'database_type' => 'postgres',
                    'mariadb' => 0,
                ]),
                'response' => $this->response([
                    'size' => 3,
                    'region' => 'us-west-1'
                ]),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->aws('northrend')
                        ->withSizeId(3)
                        ->usingCredential(1)
                        ->at('us-west-1')
                        ->runningPhp('7.1')
                        ->withPostgres('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'payload' => [
                    'database' => 'laravel',
                    'ip_address' => '37.139.3.148',
                    'mariadb' => 1,
                    'database_type' => 'mariadb',
                    'name' => 'northrend',
                    'php_version' => 'php71',
                    'private_ip_address' => '10.129.3.252',
                    'provider' => 'custom',
                    'size' => 13,
                ],
                'response' => [
                    'id' => 1,
                    'credential_id' => 0,
                    'name' => 'northrend',
                    'size' => 13,
                    'php_version' => 'php71',
                    'ip_address' => '37.139.3.148',
                    'private_ip_address' => '10.129.3.252',
                    'blackfire_status' => null,
                    'papertail_status' => null,
                    'revoked' => false,
                    'created_at' => '2016-12-15 18:38:18',
                    'is_ready' => false,
                    'network' => [],
                    'provision_command' => 'echo 1',
                ],
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->custom('northrend')
                        ->withSizeId(13)
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->usingPublicIp('37.139.3.148')
                        ->usingPrivateIp('10.129.3.252')
                        ->save();
                },
            ],
        ];
    }

    public function createServerWithDefaultCredentialDataProvider(): array
    {
        return [
            [
                'credentialId' => 2,
                'payload' => $this->payload(['credential_id' => 2]),
                'response' => $this->response(),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->droplet('northrend')
                        ->withSizeId(1)
                        ->at('fra1')
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
            [
                'credentialId' => 3,
                'payload' => $this->payload([
                    'provider' => 'linode',
                    'credential_id' => 3,
                    'size' => 1,
                    'region' => 10,
                ]),
                'response' => $this->response(['size' => 1]),
                'factory' => function (Forge $forge) {
                    return $forge
                        ->create()
                        ->linode('northrend')
                        ->withSizeId(1)
                        ->at(10)
                        ->runningPhp('7.1')
                        ->withMariaDb('laravel')
                        ->asNodeBalancer()
                        ->connectedTo([1, 2, 3])
                        ->save();
                },
            ],
        ];
    }
}
