<?php

namespace Vos\RaffleServer\Exception;

use Exception;

final class UnexpectedDataException extends Exception implements UserException
{
    private const MSG = 'Unexpected data';

    public function __construct(?string $msg = null)
    {
        parent::__construct($msg ?? self::MSG);
    }

    public static function forUnsupportedMessage(string $message): self
    {
        return new self(
            self::MSG . sprintf(
                ': message [%s] is not supported',
                $message
            ),
        );
    }

    public static function forMissingKey(string $missingKey): self
    {
        return new self(
            self::MSG . sprintf(
                ': message should contain key [%s]',
                $missingKey
            )
        );
    }
}
