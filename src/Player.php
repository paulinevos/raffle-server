<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;

final class Player
{
    public function __construct(public readonly string $username, public readonly ConnectionInterface $connection)
    {
    }
}