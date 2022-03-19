<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Vos\RaffleServer\Exception\PlayerException;

final class Raffler implements MessageComponentInterface
{
    private int $connections = 0;
    private readonly RafflePool $pool;
    private readonly MessageHandler $messageHandler;

    public function __construct(
        private readonly Options $options,
    ) {
        $this->pool = new RafflePool($this->options->maxPlayers, $this->options->timeout);
        $this->messageHandler = new MessageHandler($this->pool);
    }

    function onMessage(ConnectionInterface $connection, $msg): void
    {
        try {
            $this->messageHandler->handleIncoming($msg, $connection);
        } catch (PlayerException $e) {
            $connection->send(json_encode(
                ['error' => $e->getMessage()]
            ));
        }
    }

    function onOpen(ConnectionInterface $conn): void
    {
        if ($this->connections >= $this->options->maxConnections) {
            $conn->send('Too many connections! Sorry.');
            $conn->close();
        }
        $this->connections++;
    }

    function onClose(ConnectionInterface $conn): void
    {
        $this->connections--;

        if ($this->pool->isHost($conn)) {
            $this->pool->close();
            return;
        }

        $this->pool->removePlayer($conn);
    }

    function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->send($e->getMessage());
        $conn->close();
    }
}