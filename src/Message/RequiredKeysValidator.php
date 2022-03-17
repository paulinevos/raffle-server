<?php

namespace Vos\RaffleServer\Message;

use Vos\RaffleServer\Exception\UnexpectedDataException;

final class RequiredKeysValidator
{
    public static function ensureContains(array $data, string ...$keys) {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw UnexpectedDataException::forMissingKey($key);
            }
        }
    }
}