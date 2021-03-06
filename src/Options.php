<?php

namespace Vos\RaffleServer;

final class Options
{
    private const DEFAULT_MAX_CONNECTIONS = 100;
    private const DEFAULT_MAX_PLAYERS = 100;
    private const DEFAULT_TIMEOUT = 3600;
    private const DEFAULT_PORT = 8080;
    private const DEFAULT_HOST_KEY = 'admin';

    public function __construct(
        public readonly int $maxConnections,
        public readonly int $maxPlayers,
        public readonly int $timeout,
        public readonly int $port,
        public readonly string $hostKey,
    ) {
    }

    public static function fromArray(array $options): self
    {
        return new self(
            $options['max-conn'] ?? self::DEFAULT_MAX_CONNECTIONS,
            $options['max-player'] ?? self::DEFAULT_MAX_PLAYERS,
            $options['timeout'] ?? self::DEFAULT_TIMEOUT,
            $options['port'] ?? self::DEFAULT_PORT,
            $options['password'] ?? self::DEFAULT_HOST_KEY,
        );
    }
}
