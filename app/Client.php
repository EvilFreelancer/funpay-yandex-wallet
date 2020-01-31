<?php

namespace App;

class Client
{
    /**
     * Emulator endpoint URL
     *
     * @var string
     */
    private $endpoint = 'https://funpay.ru/yandex/emulator';

    /**
     * Verbose output from remote
     *
     * @var bool
     */
    public $verbose = false;

    /**
     * Enable debug mode (it will include header of response to response body)
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Method of request
     *
     * @var string
     */
    public $method = 'post';

    /**
     * Array of form parameters, which should be sent
     *
     * @var array
     */
    public $parameters = [];

    /**
     * Validate parameters before execution
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate(): bool
    {
        if ($this->method !== 'post') {
            throw new \InvalidArgumentException('Method of query is incorrect');
        }
        if (empty($this->parameters)) {
            throw new \InvalidArgumentException('Array of parameters is empty');
        }
        return true;
    }

    /**
     * Execute query to remote server
     *
     * @return string
     * @throws \InvalidArgumentException|\ErrorException
     */
    public function doRequest(): string
    {
        // Validate inputs
        $this->validate();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->verbose);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Need for custom exception on failure
        curl_setopt($curl, CURLOPT_HEADER, $this->debug);       // Exclude header from results
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->parameters));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'content-type: application/x-www-form-urlencoded; charset=UTF-8',
            'x-requested-with: XMLHttpRequest' // Hello from this place to creator of this test task ;)
        ]);
        curl_setopt($curl, CURLOPT_URL, $this->endpoint);
        $result = curl_exec($curl);

        // Throw exception if response is invalid
        if (!$result) {
            throw new \ErrorException('Invalid response from remote server');
        }

        return $result;
    }
}
