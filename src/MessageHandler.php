<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Vos\RaffleServer\Exception\InvalidHostKey;
use Vos\RaffleServer\Exception\UserActionNotAllowedException;
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
    public function handleIncoming(string $msg, ConnectionInterface $connection, Options $options): void
    {
        if (trim($msg) === 'ping') {
            $connection->send('pong');
            return;
        }

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
                $this->pool->start($host->joinCode, $host->hostKey, $options->hostKey, $connection);
                break;
            case 'registerPlayer':
                $registerPlayer = RegisterPlayer::fromData($data);
                $player = new Player($registerPlayer->username, $connection);
                $this->pool->addPlayer($registerPlayer->joinCode, $player);
                break;
            case 'pickWinner':
                if (!$this->pool->isHost($connection)) {
                    throw UserActionNotAllowedException::forPickingWinner();
                }
                $this->pool->pickWinner();
                break;
            default:
                throw new UnexpectedDataException();
        }
    }
}
