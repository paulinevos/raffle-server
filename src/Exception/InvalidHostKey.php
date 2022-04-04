<?php

namespace Vos\RaffleServer\Exception;

use Exception;

final class InvalidHostKey extends Exception implements UserException
{
    public static function forKey(): self
    {
        return new self('Provided host key is invalid');
    }
}
