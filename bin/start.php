<?php

use Ratchet\App;
use Vos\RaffleServer\MessageHandler;
use Vos\RaffleServer\Raffler;

require_once './vendor/autoload.php';

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

$app = new App('localhost', 8080);
$app->route('/', new Raffler(new MessageHandler()), ['*']);

echo "Starting raffle server at localhost:8080\n";

$app->run();
