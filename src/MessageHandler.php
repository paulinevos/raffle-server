<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Vos\RaffleServer\Exception\UnexpectedDataException;

final class MessageHandler
{
    private ?RafflePool $pool = null;

    public function handleIncoming(string $msg, ConnectionInterface $from): void
    {
        $data = json_decode($msg, true);
        $message = $data['message'] ?? null;

        if (!$message) {
            throw new UnexpectedDataException('No message type specified');
        }

        switch ($message) {
            case 'registerHost':
                $this->pool = RafflePool::start($from);
                break;
            case 'registerPlayer':
                $this->ensureRafflePool();

                $player = new Player($data['username'], $from);
                $this->pool->addPlayer($data['joinCode'], $player);
                break;
            case 'pickWinner':
                $this->ensureRafflePool();

                if ($this->pool->host !== $from) {
                    throw new \Exception('Only the host can pick a winner');
                }
                $this->pool->pickWinner();
                break;
            default:
                throw new UnexpectedDataException();
        }
    }

    private function ensureRafflePool(): void
    {
        if (null === $this->pool) {
            throw new \Exception('No active raffle pool');
        }
    }
}
