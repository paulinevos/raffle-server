<?php

namespace Vos\RaffleServer\Exception;

use Exception;

final class AddPlayerException extends Exception implements PlayerException
{
    public static function forInactivePool(): self
    {
        return new self('No active raffle pool');
    }

    public static function forRafflePoolFull(): self
    {
        return new self('Sorry, the raffle pool is full!');
    }

    public static function forIncorrectJoinCode(string $joinCode): self
    {
        return new self('No active raffle pool for code ' . $joinCode);
    }

    public static function forDuplicateUsername(string $username): self
    {
        return new self(sprintf('Username "%s" already taken', $username));
    }
}