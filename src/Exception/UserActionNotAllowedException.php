<?php

namespace Vos\RaffleServer\Exception;

use Exception;

final class UserActionNotAllowedException extends Exception implements UserException
{
    public static function forPickingWinner(): self
    {
        return new self('Only a host can pick a winner');
    }

    public static function forStartingSecondPool(): self
    {
        return new self('Can\'t start raffle pool as there\'s already one running');
    }
}