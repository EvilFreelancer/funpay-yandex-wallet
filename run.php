<?php
require_once __DIR__ . '/vendor/autoload.php';

// Init client
$client = new \App\Client();

// Set query parameters and make query
$response = $client->getResponse([
    'receiver' => '4100175017397',
    'sum'      => '555',
]);

var_dump($response);
