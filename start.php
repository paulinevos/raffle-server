<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Vos\RaffleServer\Options;
use Vos\RaffleServer\Raffler;

require_once './vendor/autoload.php';

$options = parseOptions($argv);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Raffler(
                $options,
            ),
        ),
    ),
    $options->port
);

$server->run();

function parseOptions(array $argv): Options
{
    array_shift($argv);

    foreach ($argv as $option) {
        $exploded = explode('=', $option);

        if (count($exploded) === 2) {
            $options[$exploded[0]] = $exploded[1];
        }
    }

    return Options::fromArray($options ?? []);
}