<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Action;

use Nanofelis\Bundle\JsonRpcBundle\Tests\Service\MockService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class DocTest extends WebTestCase
{
    public static KernelBrowser $client;

    private RouterInterface $router;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $this->router = self::$client->getContainer()->get('router');
    }

    public function testDoc()
    {
        $crawler = self::$client->request('GET', $this->router->generate('nanofelis_json_rpc.doc'), [], [], [], '@');

        $this->assertTrue(self::$client->getResponse()->isSuccessful());
        $this->assertSame(MockService::getServiceKey(), $crawler->filterXPath('//a')->text());
    }
}
