<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Vos\RaffleServer\MessageHandler;
use Vos\RaffleServer\RafflePool;
use Vos\RaffleServer\Raffler;

require_once './vendor/autoload.php';

$pool = new RafflePool();
$handler = new MessageHandler($pool);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Raffler($pool, $handler),
        )
    ),
    8080
);

$server->run();