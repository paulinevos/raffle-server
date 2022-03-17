<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Vos\RaffleServer\Exception\PlayerException;

final class Raffler implements MessageComponentInterface
{
    public function __construct(
        private readonly RafflePool $pool,
        private readonly MessageHandler $messageHandler
    ) {
    }

    function onMessage(ConnectionInterface $connection, $msg)
    {
        try {
            $this->messageHandler->handleIncoming($msg, $connection);
        } catch (PlayerException $e) {
            $connection->send(json_encode(
                ['error' => $e->getMessage()]
            ));
        }
    }

    function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";
    }

    function onClose(ConnectionInterface $conn)
    {
        if ($this->pool->isHost($conn)) {
            $this->pool->close();
        }
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send($e->getMessage());
        $conn->close();
    }
}