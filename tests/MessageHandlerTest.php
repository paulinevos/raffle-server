<?php

namespace Vos\RaffleServer;

use Monolog\Handler\TestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Ratchet\ConnectionInterface;
use Vos\RaffleServer\Exception\UserActionNotAllowedException;
use Vos\RaffleServer\Exception\UnexpectedDataException;
use PHPUnit\Framework\TestCase;

final class MessageHandlerTest extends TestCase
{
    private RafflePool $pool;
    private ConnectionInterface|MockObject $connection;
    private TestHandler $logHandler;

    protected function setUp(): void
    {
        $this->pool = new RafflePool(2);
        $this->logHandler = new TestHandler();
        $this->connection = new MockConnection($this->logHandler);
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

        $this->assertTrue($this->logHandler->hasInfoThatContains('joinedRaffle'));
    }

    /**
     * @test
     */
    public function handlesPickWinner(): void
    {
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
        $this->assertTrue($this->logHandler->hasInfoThatContains('You won!'));
    }

    /**
     * @test
     */
    public function handlesPing(): void /*by doing nothing...*/
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
