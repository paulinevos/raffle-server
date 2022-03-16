<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;

final class Player
{
    public function __construct(
        public readonly string $username,
        private readonly ConnectionInterface $connection
    ) {}

    public function send(string $msg): void
    {
        $this->connection->send($msg);
    }
}
