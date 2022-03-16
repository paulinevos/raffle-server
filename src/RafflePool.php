<?php

namespace Vos\RaffleServer;

use Ratchet\ConnectionInterface;
use Exception;
use Vos\RaffleServer\Exception\AddUserException;

final class RafflePool
{
    /**
     * @var Player[]
     */
    private array $players = [];
    public function __construct(
        public readonly string $joinCode,
        public readonly ConnectionInterface $host
    ) {}

    public static function start(ConnectionInterface $host): self
    {
        $code = '1234';
        $pool = new RafflePool($code, $host);
        $pool->host->send(json_encode(['message' => 'raffleStarted', 'joinCode' => $code]));
        echo "Raffle pool started with code " . $code . PHP_EOL;

        return $pool;
    }

    public function pickWinner(): void
    {
        if (count($this->players) < 1) {
            throw new Exception('There\'s no players :/');
        }

        $players = $this->players;
        shuffle($players);
        $winner = $players[0];

        echo sprintf("🏆 And the winner is... %s!\n", $winner->username);

        foreach ($this->players as $player) {
            if ($winner === $player) {
                $player->send(json_encode(['message' => 'You won!']));
                continue;
            }
            $player->send(json_encode(['message' => 'Better luck next time...']));
        }

        $this->host->send(
            json_encode(
                [
                    'message' => 'winner',
                    'username' => $winner->username,
                ]
            )
        );
    }

    public function addPlayer(string $joinCode, Player $player): void
    {
        if ($this->joinCode !== $joinCode) {
            throw AddUserException::forIncorrectJoinCode($joinCode);
        }

        $hash = base64_encode($player->username);
        if (isset($this->players[$hash])) {
            throw AddUserException::forDuplicateUsername($player->username);
        }

        echo "Adding player to raffle pool\n";
        $this->host->send(json_encode(['message' => 'newPlayer', 'username' => $player->username,]));
        $player->send(json_encode(['message' => 'joinedRaffle', 'username' => $player->username]));
        $this->players[base64_encode($player->username)] = $player;
    }
}
