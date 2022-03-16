<?php

namespace Vos\RaffleServer;

final class MessageHandler
{
    public function handleIncoming(string $msg): void
    {
        echo "Incoming message: $msg\n";
    }
}
