<?php

namespace Vos\RaffleServer;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;

final class MockConnection implements ConnectionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?HandlerInterface $testLogHandler = null)
    {
        $handler = $testLogHandler ?? new TestHandler();
        $this->logger = new Logger('test', [$handler]);
    }

    public function send($data) {
        $this->logger->info($data);
    }

    public function close() {
        $this->logger->info('Connection closed');
    }
}