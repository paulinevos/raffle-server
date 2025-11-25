<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Vos\RaffleServer\Options;
use Vos\RaffleServer\Raffler;

require_once './vendor/autoload.php';

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
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

echo 'Listening on port ' . $options->port . PHP_EOL;
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
