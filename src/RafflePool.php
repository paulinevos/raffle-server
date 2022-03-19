<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Exception;
use Vos\RaffleServer\Exception\AddPlayerException;
use Vos\RaffleServer\Exception\PlayerActionNotAllowedException;

final class RafflePool
{
    /**
     * @var Player[]
     */
    private array $players = [];
    private ?string $joinCode = null;
    private ?ConnectionInterface $host = null;

    public function __construct(private readonly int $maxPlayers)
    {
    }

    public function isActive(): bool
    {
        return $this->joinCode !== null && $this->host !== null;
    }

    public function close(): void
    {
        foreach ($this->players as $player) {
            $player->connection->send('Raffle closed by host. Bye bye! 👋');
            $player->connection->close();
        }

        if ($this->host) {
            $this->host->send('Raffle closed');
            $this->host->close();
        }

        $this->players = [];
        $this->joinCode = null;
        $this->host = null;
        echo "Pool's closed\n";
    }

    public function playerCount(): int
    {
        return count($this->players);
    }

    public function pickWinner(): void
    {
        if (count($this->players) < 1) {
            throw new Exception('There\'s no players bestie :/');
        }

        $players = $this->players;
        shuffle($players);
        $winner = $players[0];

        echo sprintf("🏆 And the winner is... %s!\n",$winner->username);
        $this->notifyPlayers($winner);
        $this->notifyHost(
            json_encode([
                'message' => 'winner',
                'connection' => $winner->username,
            ])
        );
    }

    private function notifyPlayers(Player $winner): void
    {
        foreach ($this->players as $player) {
            if ($winner === $player) {
                $player->connection->send('You won!');
            } else {
                $player->connection->send('Better luck next time...');
            }
        }
    }

    private function notifyHost(string $msg): void
    {
        $this->ensureHostPresent();
        $this->host->send($msg);
    }

    /**
     * @throws Exception
     */
    private function ensureHostPresent(): void
    {
        if ($this->host === null) {
            throw new Exception('No host is present');
        }
    }

    public function start(string $joinCode, ConnectionInterface $host)
    {
        if ($this->isActive()) {
            throw new Exception('Can\'t start raffle as it seems to be active?');
        }

        if (count($this->players) > 0) {
            throw new Exception('There shouldn\'t be any connections in the pool before starting');
        }

        $this->joinCode = $joinCode;
        $this->host = $host;
        $this->notifyHost(json_encode(['message' => 'raffleStarted']));
    }

    public function isHost(ConnectionInterface $host): bool {
        return $this->host !== null && $this->host === $host;
    }

    public function addPlayer(string $joinCode, Player $player): void
    {
        if (!$this->isActive()) {
            throw AddPlayerException::forInactivePool();
        }

        if (count($this->players) >= $this->maxPlayers) {
            throw AddPlayerException::forRafflePoolFull();
        }

        if ($this->joinCode !== $joinCode) {
            throw AddPlayerException::forIncorrectJoinCode($joinCode);
        }

        $hash = base64_encode($player->username);
        if (isset($this->players[$hash])) {
            throw AddPlayerException::forDuplicateUsername($player->username);
        }

        echo "Adding player to raffle pool\n";
        $this->notifyHost(json_encode(['message' => 'newPlayer', 'username'=> $player->username,]));
        $this->players[base64_encode($player->username)] = $player;
    }

    public function removePlayer(ConnectionInterface $connection): void
    {
        foreach ($this->players as $key => $player) {
            if ($player->connection === $connection) {
                unset($this->players[$key]);
                $this->notifyHost(json_encode([
                    'message' => 'playerLeft',
                    'username'=> $player->username,
                ]));
                return;
            }
        }

        throw new Exception('Can\'t remove player that isn\'t there');
    }

    private function compileErrorMessage(string $message): string
    {
        return json_encode(['error' => $message]) . PHP_EOL;
    }
}