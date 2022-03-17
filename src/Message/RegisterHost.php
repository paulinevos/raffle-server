<?php

namespace Vos\RaffleServer\Message;

use Vos\RaffleServer\Exception\UnexpectedDataException;

final class RegisterHost
{
    private const KEY_CODE = 'joinCode';
    private const KEY_HOST_PASSWORD = 'hostKey';
    public const MUST_CONTAIN = [self::KEY_CODE, self::KEY_HOST_PASSWORD];

    public function __construct(public readonly string $joinCode,){}

    /**
     * @throws UnexpectedDataException
     */
    public static function fromData(array $data) {
        RequiredKeysValidator::ensureContains($data, ...self::MUST_CONTAIN);
        return new self(
            $data[self::KEY_CODE],
        );
    }
}