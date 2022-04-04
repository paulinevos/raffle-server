<?php

namespace Vos\RaffleServer;

use PHPUnit\Framework\MockObject\MockObject;
use Ratchet\ConnectionInterface;
use Vos\RaffleServer\Exception\UserActionNotAllowedException;
use Vos\RaffleServer\Exception\UnexpectedDataException;
use PHPUnit\Framework\TestCase;

final class MessageHandlerTest extends TestCase
{
    private RafflePool $pool;
    private ConnectionInterface|MockObject $connection;

    protected function setUp(): void
    {
        $this->pool = new RafflePool(2);
        $this->connection = new MockConnection();
    }

    /**
     * @test
     */
    public function handlesRegisterHost(): void
    {
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'admin',
            'joinCode' => '1234',
        ]));

        $this->assertTrue($this->pool->isActive());
    }

    /**
     * @test
     */
    public function handlesRegisterPlayer(): void
    {
        $this->expectNotToPerformAssertions();

        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'admin',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode([
            'message' => 'registerPlayer',
            'username' => 'pookie',
            'joinCode' => '1234'
        ]));
    }

    /**
     * @test
     */
    public function handlesPickWinner(): void
    {
        $this->expectNotToPerformAssertions();
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'admin',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode([
            'message' => 'registerPlayer',
            'username' => 'pookie',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode(['message' => 'pickWinner']));
    }

    /**
     * @test
     */
    public function handlesPing(): /*by doing nothing...*/ void
    {
        $this->handleMessage('ping');
        $this->assertTrue($this->logHandler->hasInfo('pong'));
    }

    /**
     * @test
     */
    public function failsIfPlayerAttemptsToPickWinner(): void
    {
        $this->expectException(UserActionNotAllowedException::class);
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'admin',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode([
            'message' => 'registerPlayer',
            'username' => 'pookie',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode(['message' => 'pickWinner']), new MockConnection());
    }

    /**
     * @test
     */
    public function failsIfMessageNotSpecified(): void
    {
        $this->expectException(UnexpectedDataException::class);
        $this->handleMessage(json_encode(['joinCode' => '1234']));
    }

    /**
     * @test
     */
    public function failsForUnsupportedMessage(): void
    {
        $this->expectException(UnexpectedDataException::class);
        $this->handleMessage(json_encode(['message' => '🥦']));
    }

    /**
     * @test
     */
    public function failsForInvalidRegisterHostMessage(): void
    {
        $this->expectException(UnexpectedDataException::class);
        $this->handleMessage(json_encode(['message' => 'registerHost']));
    }

    /**
     * @test
     */
    public function failsForInvalidRegisterPlayerMessage(): void
    {
        $this->expectException(UnexpectedDataException::class);
        $this->getMessageHandler()->handleIncoming(
            json_encode(['message' => 'registerPlayer']),
            $this->createMock(ConnectionInterface::class),
            Options::fromArray([])
        );
    }

    private function getMessageHandler(): MessageHandler
    {
        return new MessageHandler($this->pool);
    }

    private function handleMessage(string $msg, ?ConnectionInterface $connection = null, ?Options $options = null): void
    {
        $this->getMessageHandler()->handleIncoming(
            $msg,
            $connection ?? $this->connection,
            $options ?? Options::fromArray([]),
        );
    }
}
