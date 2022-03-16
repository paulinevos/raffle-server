<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

final class Raffler implements MessageComponentInterface
{
    private \SplObjectStorage $clients;

    public function __construct(private readonly MessageHandler $messageHandler)
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            echo "Message received: {$msg}\n";
            $this->messageHandler->handleIncoming($msg, $from);
        } catch (\Exception $e) {
            $from->send(json_encode(['error' => 'Something went wrong: ' . $e->getMessage()]));
        }
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Throwable $e): void
    {
        $conn->close();
    }
}
