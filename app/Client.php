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
     * List of allowed keys of parameters
     */
    public const ALLOWED_PARAMETERS = [
        'receiver',
        'sum'
    ];

    /**
     * Validate parameters before execution
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateParameters(): bool
    {
        if ($this->method !== 'post') {
            throw new \InvalidArgumentException('Method of query is incorrect');
        }
        if (empty($this->parameters)) {
            throw new \InvalidArgumentException('Array of parameters is empty');
        }
        if (\count($this->parameters) > count(self::ALLOWED_PARAMETERS)) {
            throw new \InvalidArgumentException('Count of allowed parameters is invalid');
        }

        foreach ($this->parameters as $name => $value) {
            if (!\in_array($name, self::ALLOWED_PARAMETERS, true)) {
                throw new \InvalidArgumentException('Parameter "' . $name . '" is not in allowed list: [' . implode(', ', self::ALLOWED_PARAMETERS) . ']');
            }
        }

        foreach ($this->parameters as $name => $value) {
            if (!\in_array($name, self::ALLOWED_PARAMETERS, true)) {
                throw new \InvalidArgumentException('Parameter "' . $name . '" is not in allowed list: [' . implode(', ', self::ALLOWED_PARAMETERS) . ']');
            }
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
        $this->validateParameters();

        // Initial the CURL client (btw, first version was on file_get_content with custom stream_context, but I like curl more)
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, $this->verbose);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Need for custom exception on failure
        curl_setopt($curl, CURLOPT_HEADER, $this->debug); // Exclude header from results
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->parameters));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'content-type: application/x-www-form-urlencoded; charset=UTF-8',
            'x-requested-with: XMLHttpRequest' // Hello from Paul to creator of this test task, awesome check for script kiddies ;)
        ]);
        curl_setopt($curl, CURLOPT_URL, $this->endpoint);
        $response = curl_exec($curl);

        // Throw exception if response is invalid
        if (!$response) {
            throw new \ErrorException('Invalid response from remote server');
        }

        return $response;
    }

    /**
     * Convert string returned from emulator to array
     *
     * TODO: This method should be in separated class, which will extend abstract Client class
     *
     * @param string $response
     *
     * @return array
     * @throws \InvalidArgumentException|\ErrorException
     */
    public function parseResponse(string $response): array
    {
        $count = mb_substr_count($response, "\n");

        if ($count <= 1 || $count >= 3) {
            throw new \ErrorException('Invalid response from remote, valid response should have only 3 lines: ' . $response);
        }

        // Convert string ot array of string
        $strings = explode("\n", $response);

        // Resulted array
        $result = [];

        foreach ($strings as $string) {

            // Check for number
            preg_match("/(.*) {$this->parameters['receiver']}/", $string, $matches);
            if (!empty($matches)) {
                $result['receiver'] = $this->parameters['receiver'];
                continue;
            }

            // Check for password
            preg_match('/(.*): (.*)/', $string, $matches);
            if (!empty($matches)) {
                $password           = $matches[2] ?? filter_var($string, FILTER_SANITIZE_NUMBER_INT);
                $result['password'] = (int) $password;
                unset($password);
                continue;
            }

            // Check for sum and currency
            preg_match('/(.*) ((\$)?)(\d+),(\d+)((kr|\$|£|€|р\.)?)/u', $string, $matches);
            if (!empty($matches)) {
                $result['amount']   = (float) ($matches[4] . '.' . $matches[5]);
                $result['currency'] = !empty($matches[6]) ? $matches[6] : $matches[2]; // nope, check like: $matches[6] ?? $matches[2] is not useful here
                continue;
            }
        }

        return $result;
    }

    /**
     * Get response from remote server in array format or throw exception
     *
     * TODO: This method should be in separated class, which will extend abstract Client class
     *
     * @param array $parameters
     *
     * @return array
     * @throws \InvalidArgumentException|\ErrorException
     */
    public function getResponse(array $parameters): array
    {
        // Set parameters before begin
        $this->parameters = $parameters;

        // Execute query to remote
        $response = $this->doRequest();

        // Parse response from few lines to normal array of strings
        return $this->parseResponse($response);
    }
}
