<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Vos\RaffleServer\Exception\UserException;
use Vos\RaffleServer\Exception\PlayerNotFoundException;

final class Raffler implements MessageComponentInterface
{
    private int $connections = 0;
    private readonly RafflePool $pool;
    private readonly MessageHandler $messageHandler;

    public function __construct(
        private readonly Options $options,
    ) {
        $this->pool = new RafflePool($this->options->maxPlayers);
        $this->messageHandler = new MessageHandler($this->pool);
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            echo "Message received: {$msg}\n";
            $this->messageHandler->handleIncoming($msg, $from, $this->options);
        } catch (UserException $e) {
            $from->send(
                json_encode(
                    ['error' => $e->getMessage()]
                )
            );
        } catch (\Exception $e) {
            $from->send(json_encode(['error' => 'Server error: ' . $e->getMessage()]));
            throw $e;
        }
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        if ($this->connections >= $this->options->maxConnections) {
            $conn->send(json_encode(['error' => 'Too many connections! Sorry.']));
            $conn->close();
            return;
        }
        $this->connections++;
    }

    public function onClose(ConnectionInterface $conn): void
    {
        if ($this->pool->isHost($conn)) {
            $this->pool->close();
            return;
        }

        try {
            $this->pool->removePlayer($conn);
        } catch (PlayerNotFoundException $e) {
            // Do nothing, connection closed before player joined.
        }

        $this->connections--;
    }

    public function onError(ConnectionInterface $conn, \Throwable $e): void
    {
        $conn->send(json_encode(['error' => $e->getMessage()]));
        $conn->close();
    }
}
