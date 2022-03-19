<?php

namespace Vos\RaffleServer\Exception;

use Exception;

final class PlayerActionNotAllowedException extends Exception implements PlayerException
{
    public static function forPickingWinner(): self
    {
        return new self('Only a host can pick a winner');
    }

    public static function forDuplicateUsername(string $username): self
    {
        return new self(sprintf('Username "%s" already taken', $username));
    }
}