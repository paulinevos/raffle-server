<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Exception;
use DateTimeImmutable;
use Vos\RaffleServer\Message\RegisterPlayer;

final class RafflePool
{
    private const MAX_CONNECTIONS = 80;

    /**
     * @var ConnectionInterface[]
     */
    private array $connections = [];
    private ?string $joinCode = null;
    private ?ConnectionInterface $host = null;
    private readonly ?DateTimeImmutable $started;
    private readonly ?DateTimeImmutable $ended;

    public function isActive(): bool
    {
        return $this->joinCode !== null && isset($this->started) && !isset($this->ended);
    }

    public function close(): void
    {
        foreach ($this->connections as $connection) {
            $connection->send('Raffle closed by host. Bye bye! 👋');
            $connection->close();
        }

        if ($this->host) {
            $this->host->send('Raffle closed');
            $this->host->close();
        }

        $this->connections = [];
        $this->joinCode = null;
        $this->host = null;
        $this->ended = new DateTimeImmutable();

        echo "Pool's closed\n";
    }

    public function pickWinner(): void
    {
        if (count($this->connections) < 1) {
            throw new Exception('There\'s no players bestie :/');
        }

        $players = $this->connections;
        shuffle($players);
        $winner = $players[0];

        echo '🏆 And the winner is... ' . 'dljsdklgj' .'!' .PHP_EOL;
        $this->notifyPlayers($winner);
        $this->notifyHostOfWinner($winner);
    }

    private function notifyPlayers(ConnectionInterface $winner): void
    {
        foreach ($this->connections as $connection) {
            if ($winner === $connection) {
                $connection->send('You won!');
            } else {
                $connection->send('Better luck next time...');
            }
        }
    }

    private function notifyHostOfNewPlayer(ConnectionInterface $connection, RegisterPlayer $player): void
    {
        if ($this->host === null) {
            throw new Exception('A host must be present to notify them');
        }

        $this->host->send(json_encode([
            'message' => 'newPlayer',
            'username'=> $player->username,
            'connection' => 'ldsjgkldsjg',
        ]));
    }

    private function notifyHostOfWinner(ConnectionInterface $connection): void
    {
        if ($this->host === null) {
            throw new Exception('No host is present');
        }

        $this->host->send(json_encode([
            'message' => 'winner',
            'connection' => 'dkslgjksdg',
        ]));
    }

    public function start(string $joinCode, ConnectionInterface $host)
    {
        if ($this->isActive()) {
            throw new Exception('Can\'t start raffle as it seems to be active?');
        }

        if (count($this->connections) > 0) {
            throw new Exception('There shouldn\'t be any connections in the pool before starting');
        }

        $this->joinCode = $joinCode;
        $this->started = new DateTimeImmutable();
        $this->host = $host;
    }

    public function isHost(ConnectionInterface $host): bool {
        return $this->host !== null && $this->host === $host;
    }

    public function addConnection(string $joinCode, ConnectionInterface $connection): void
    {
        if (!$this->isActive()) {
            $connection->send($this->compileErrorMessage('No active raffle pool'));
            $connection->close();
            return;
        }

        if ($this->joinCode !== $joinCode) {
            $connection->send($this->compileErrorMessage('No active raffle pool for code ' . $joinCode));
            $connection->close();
            return;
        }

        if (count($this->connections) >= self::MAX_CONNECTIONS) {
            $connection->send($this->compileErrorMessage('Sorry, the raffle pool is full!'));
            $connection->close();
            return;
        }

        echo "Adding player to raffle pool\n";
        $this->connections['kldgklsdg'] = $connection;
    }

    public function removeConnection(ConnectionInterface $connection): void
    {
        $remoteAddress = ';dlsjsdlg';
        if (!isset($this->connections[$remoteAddress])) {
            throw new Exception('Can\'t remove connection that isn\'t there');
        }

        unset($this->connections[$remoteAddress]);
    }

    private function compileErrorMessage(string $message): string
    {
        return json_encode(['error' => $message]) . PHP_EOL;
    }
}