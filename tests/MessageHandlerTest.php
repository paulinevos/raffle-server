<?php


use PHPUnit\Framework\MockObject\MockObject;
use React\Socket\ConnectionInterface;
use Vos\RaffleServer\Exception\PlayerActionNotAllowedException;
use Vos\RaffleServer\Exception\UnexpectedDataException;
use Vos\RaffleServer\MessageHandler;
use PHPUnit\Framework\TestCase;
use Vos\RaffleServer\RafflePool;

final class MessageHandlerTest extends TestCase
{
    private RafflePool $pool;
    private ConnectionInterface|MockObject $connection;

    protected function setUp(): void
    {
        $this->pool = new RafflePool();
        $this->connection = $this->createMock(ConnectionInterface::class);
    }

    /**
     * @test
     */
    public function handlesRegisterHost(): void
    {
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'some-key',
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
            'hostKey' => 'some-key',
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
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'some-key',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode([
            'message' => 'registerPlayer',
            'username' => 'pookie',
            'joinCode' => '1234'
        ]));

        $this->connection->expects($this->exactly(6))
            ->method('getRemoteAddress')
            ->willReturn('tcp://1234');

        $this->handleMessage(json_encode(['message' => 'pickWinner']));
    }

    /**
     * @test
     */
    public function failsIfPlayerAttemptsToPickWinner(): void
    {
        $this->expectException(PlayerActionNotAllowedException::class);
        $this->handleMessage(json_encode([
            'message' => 'registerHost',
            'hostKey' => 'some-key',
            'joinCode' => '1234'
        ]));

        $this->handleMessage(json_encode([
            'message' => 'registerPlayer',
            'username' => 'pookie',
            'joinCode' => '1234'
        ]));

        $this->connection->expects($this->exactly(2))
            ->method('getRemoteAddress')
            ->willReturn('tcp://1234', 'tcp://5678');

        $this->handleMessage(json_encode(['message' => 'pickWinner']));
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
            $this->createMock(ConnectionInterface::class)
        );
    }

    private function getMessageHandler(): MessageHandler
    {
        return new MessageHandler($this->pool);
    }

    private function handleMessage(string $msg): void
    {
        $this->getMessageHandler()->handleIncoming($msg, $this->connection);
    }
}
