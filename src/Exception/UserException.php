<?php

namespace Vos\RaffleServer\Exception;

use Throwable;

/**
 * Label interface for exceptions caused by unexpected player behavior.
 * These should be caught and an error message sent to the player.
 */
interface UserException extends Throwable
{
}
