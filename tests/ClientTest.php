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

    /**
     * Test yandex wallet number
     *
     * @var string
     */
    private $number = '4100175017397';

    public function setUp(): void
    {
        $this->client = new Client();
    }

    /**
     * Array of typical valid responses
     *
     * @return array
     */
    public function dataProviderValid(): array
    {
        return [
            [
                "Пароль: 7300\nСпишется 123,62р.\nПеревод на счет {$this->number}",
                [
                    'amount'   => 123.62,
                    'currency' => 'р.',
                    'receiver' => $this->number,
                    'password' => 7300,
                ]
            ],
            [
                "Спишется 123,62р.\nПароль: 7300\nПеревод на счет {$this->number}",
                [
                    'amount'    => 123.62,
                    'currency'  => 'р.',
                    'receiver'  => $this->number,
                    'password'  => 7300,
                ]
            ],
            [
                "Перевод на счет {$this->number}\nСпишется 123,62$\nПароль: 7300",
                [
                    'amount'    => 123.62,
                    'currency'  => '$',
                    'receiver'  => $this->number,
                    'password'  => 7300,
                ]
            ],
            [
                "бла-счет {$this->number}\nбла-бло $123,62\nбла-код: 1234",
                [
                    'amount'    => 123.62,
                    'currency'  => '$',
                    'receiver'  => $this->number,
                    'password'  => 1234,
                ]
            ],
        ];
    }

    /**
     * There is not parameters
     */
    public function testValidateEx1(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->validateParameters();
    }

    /**
     * Invalid query method
     */
    public function testValidateEx2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->method = 'get';
        $this->client->validateParameters();
    }

    /**
     * Success validation
     */
    public function testValidate(): void
    {
        $this->client->parameters = [
            'receiver' => 'dummy',
            'sum'      => 123,
        ];

        // Validate parameters
        $result = $this->client->validateParameters();

        $this->assertTrue($result);
    }

    /**
     * Make dummy integration request, we just need to check what server returning any answer
     *
     * @throws \ErrorException
     */
    public function testDoRequest(): void
    {
        $this->client->parameters = [
            'receiver' => 'dummy',
            'sum'      => 123,
        ];

        $result = $this->client->doRequest();

        // Here should be "incorrect account" response, but it's not important
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider dataProviderValid
     *
     * @param string $response
     * @param array  $result
     *
     * @throws \InvalidArgumentException|\ErrorException
     */
    public function testParseResponse(string $response, array $result): void
    {
        $this->client->parameters['receiver'] = $this->number;
        $parsed                               = $this->client->parseResponse($response);
        $this->assertEquals($result, $parsed);
    }
}
