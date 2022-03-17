<?php

namespace Vos\RaffleServer\Message;

use Vos\RaffleServer\Exception\UnexpectedDataException;

final class RegisterPlayer
{
    private const KEY_CODE = 'joinCode';
    private const KEY_USERNAME = 'username';
    public const MUST_CONTAIN = [self::KEY_CODE, self::KEY_USERNAME];

    private function __construct(
        public readonly string $joinCode,
        public readonly string $username,
    ) {
    }

    /**
     * @throws UnexpectedDataException
     */
    public static function fromData(array $data) {
        RequiredKeysValidator::ensureContains($data, ...self::MUST_CONTAIN);
        return new self(
            $data[self::KEY_CODE],
            $data[self::KEY_USERNAME],
        );
    }
}