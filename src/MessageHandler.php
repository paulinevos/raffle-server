<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Vos\RaffleServer\Exception\PlayerActionNotAllowedException;
use Vos\RaffleServer\Exception\UnexpectedDataException;
use Vos\RaffleServer\Message\RegisterHost;
use Vos\RaffleServer\Message\RegisterPlayer;

final class MessageHandler
{
    private const EXIT = ['q', 'quit', 'exit', ':q'];
    public function __construct(private readonly RafflePool $pool)
    {
    }

    /**
     * @throws UnexpectedDataException
     */
    public function handleIncoming(string $msg, ConnectionInterface $connection): void
    {
        if (in_array(trim($msg), self::EXIT)) {
            $connection->close();
            return;
        }

        $data = json_decode($msg, true);
        $message = $data['message'] ?? null;

        if (!$message) {
            throw new UnexpectedDataException('Please specify the type of message');
        }

        switch ($message) {
            case 'registerHost':
                $host = RegisterHost::fromData($data);
                echo "Raffle pool started with code " . $host->joinCode . PHP_EOL;
                $this->pool->start($host->joinCode, $connection);
                break;
            case 'registerPlayer':
                $player = RegisterPlayer::fromData($data);
                $this->pool->addConnection($player->joinCode, $connection);
                break;
            case 'pickWinner':
                if (!$this->pool->isHost($connection)) {
                    throw PlayerActionNotAllowedException::forPickingWinner();
                }
                $this->pool->pickWinner();
                break;
            default:
                throw new UnexpectedDataException();
        }
    }
}