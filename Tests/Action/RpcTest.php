<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Action;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class RpcTest extends WebTestCase
{
    public static KernelBrowser $client;

    private RouterInterface $router;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $this->router = self::$client->getContainer()->get('router');
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testInvalidJson()
    {
        self::$client->request('POST', $this->router->generate('nanofelis_json_rpc.endpoint'), [], [], [], '@');
        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => AbstractRpcException::PARSE,
                'message' => AbstractRpcException::MESSAGES[AbstractRpcException::PARSE],
                'data' => null,
            ],
            'id' => null,
        ];
        $this->assertSame($expected, json_decode(self::$client->getResponse()->getContent(), true));
    }

    /**
     * @dataProvider provideRpcRequest
     */
    public function testRpc(array $requestData, array $expected)
    {
        self::$client->request('POST', $this->router->generate('nanofelis_json_rpc.endpoint'), [], [], [], json_encode($requestData));

        $this->assertSame($expected, json_decode(self::$client->getResponse()->getContent(), true));
    }

    public function provideRpcRequest(): \Generator
    {
        // Test regular rpc request
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['arg1' => 1, 'arg2' => 2], 'id' => 'test'],
            ['jsonrpc' => '2.0', 'result' => 3, 'id' => 'test'],
        ];

        // Test regular rpc request with params in wrong order
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['arg2' => 1, 'arg1' => 3], 'id' => 'test'],
            ['jsonrpc' => '2.0', 'result' => 4, 'id' => 'test'],
        ];

        // Test request resolver
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.requestValueResolver', 'params' => ['date' => '2017/01/01'], 'id' => 'test'],
            ['jsonrpc' => '2.0', 'result' => 'GET', 'id' => 'test'],
        ];

        // Test batch of regular rpc request
        yield [
            [
                ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['arg1' => 1, 'arg2' => 2], 'id' => 'test_0'],
                ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['arg1' => 3, 'arg2' => 4], 'id' => 'test_1'],
            ],
            [
                ['jsonrpc' => '2.0', 'result' => 3, 'id' => 'test_0'],
                ['jsonrpc' => '2.0', 'result' => 7, 'id' => 'test_1'],
            ],
        ];

        // Test rpc request with an array parameter
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.arrayParam', 'params' => ['a' => [1, 2], 'b' => 3]],
            ['jsonrpc' => '2.0', 'result' => [1, 2, 3], 'id' => null],
        ];

        // Test rpc method which returns an object
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.returnObject'],
            ['jsonrpc' => '2.0', 'result' => ['prop' => 'test'], 'id' => null],
        ];

        // Test unknown method
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.unknownMethod', 'id' => 'test'],
            ['jsonrpc' => '2.0', 'error' => [
                'code' => AbstractRpcException::METHOD_NOT_FOUND,
                'message' => AbstractRpcException::MESSAGES[AbstractRpcException::METHOD_NOT_FOUND],
                'data' => null,
            ], 'id' => 'test'],
        ];

        // Test wrong parameter type
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['arg1' => '', 'arg2' => 2], 'id' => 'test'],
            ['jsonrpc' => '2.0', 'error' => [
                'code' => AbstractRpcException::INVALID_PARAMS,
                'message' => AbstractRpcException::MESSAGES[AbstractRpcException::INVALID_PARAMS],
                'data' => null,
            ], 'id' => 'test'],
        ];

        // Test wrong params names
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.add', 'params' => ['wrongParam' => 1]],
            ['jsonrpc' => '2.0', 'error' => [
                'code' => AbstractRpcException::INVALID_PARAMS,
                'message' => AbstractRpcException::MESSAGES[AbstractRpcException::INVALID_PARAMS],
                'data' => null,
            ], 'id' => null],
        ];

        // Test application exception handling
        yield [
            ['jsonrpc' => '2.0', 'method' => 'mockService.willThrowException'],
            ['jsonrpc' => '2.0', 'error' => [
                'code' => 99,
                'message' => 'it went wrong',
                'data' => null,
            ], 'id' => null],
        ];
    }
}
