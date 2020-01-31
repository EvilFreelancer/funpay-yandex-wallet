<?php declare(strict_types=1);

namespace App\Tests;

use App\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var \App\Client
     */
    private $client;

    public function setUp(): void
    {
        $this->client = new Client();
    }

    /**
     * There is not parameters
     */
    public function testValidateEx1(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->validate();
    }

    /**
     * Invalid query method
     */
    public function testValidateEx2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->method = 'get';
        $this->client->validate();
    }

    public function testValidate(): void
    {
        $this->client->parameters = [
            'receiver' => 'dummy',
            'sum'      => 123,
        ];

        // Validate parameters
        $result = $this->client->validate();

        $this->assertTrue($result);
    }

    public function testDoRequest(): void
    {
        $this->client->parameters = [
            'receiver' => 'dummy',
            'sum'      => 123,
        ];

        $result = $this->client->doRequest();

        // Here should be "incorrect account" response, but it's not important
        $this->assertEquals('Кошелек Яндекс.Денег указан неверно.', $result);
    }
}
