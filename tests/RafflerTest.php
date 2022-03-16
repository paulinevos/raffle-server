<?php

namespace Vos\RaffleServer;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

final class RafflerTest extends TestCase
{
    private Raffler $raffler;
    private HandlerInterface $logHandler;

    protected function setUp(): void
    {
        $this->raffler = new Raffler(
            Options::fromArray([
                'max-conn' => 1,
                'password' => '5678',
            ])
        );
        $this->logHandler = new TestHandler();
    }

    /**
     * @test
     */
    public function closesPoolWhenHostLeaves(): void
    {
        $host = new MockConnection($this->logHandler);
        $this->raffler->onOpen($host);
        $this->raffler->onMessage($host, json_encode([
            'message' => 'registerHost',
            'hostKey' => '5678',
            'joinCode' => '1234'
        ]));

        $this->assertTrue($this->logHandler->hasInfoThatContains('raffleStarted'));
        $this->raffler->onClose($host);
        $this->assertTrue($this->logHandler->hasInfoThatContains('raffleEnded'));
    }

    /**
     * @test
     */
    public function allowsSecondPoolWhenFirstHasEnded(): void
    {
        $host = new MockConnection($this->logHandler);
        $this->raffler->onOpen($host);
        $this->raffler->onMessage($host, json_encode([
            'message' => 'registerHost',
            'hostKey' => '5678',
            'joinCode' => '1234'
        ]));

        $this->assertTrue($this->logHandler->hasInfoThatContains('raffleStarted'));

        $this->raffler->onMessage($host, json_encode([
            'message' => 'registerHost',
            'hostKey' => '5678',
            'joinCode' => '1234'
        ]));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Can\'t start raffle pool as there\'s already one running'
        ));

        $this->raffler->onClose($host);
        $this->assertTrue($this->logHandler->hasInfoThatContains('raffleEnded'));

        $this->logHandler->clear();

        $this->raffler->onMessage($host, json_encode([
            'message' => 'registerHost',
            'hostKey' => '5678',
            'joinCode' => '5678'
        ]));

        $this->assertTrue($this->logHandler->hasInfoThatContains('raffleStarted'));
    }

    /**
     * @test
     */
    public function closesConnectionOnMaxReached(): void
    {
        $this->raffler->onOpen(new MockConnection());
        $this->assertCount(0, $this->logHandler->getRecords());

        $this->raffler->onOpen(new MockConnection($this->logHandler));
        $this->assertCount(2, $this->logHandler->getRecords());
        $this->assertTrue($this->logHandler->hasInfo('Connection closed'));
    }

    /**
     * @test
     */
    public function allowConnectionWhenLastClosed(): void
    {
        $connection = new MockConnection();
        $this->raffler->onOpen(new MockConnection());
        $this->assertCount(0, $this->logHandler->getRecords());

        $this->raffler->onOpen(new MockConnection($this->logHandler));
        $this->assertCount(2, $this->logHandler->getRecords());
        $this->assertTrue($this->logHandler->hasInfo('Connection closed'));
        $this->logHandler->clear();

        $this->raffler->onClose($connection);
        $this->raffler->onOpen(new MockConnection($this->logHandler));

        $this->assertCount(0, $this->logHandler->getRecords());
    }

    /**
     * @test
     */
    public function failsForInvalidHostKey(): void
    {
        $host = new MockConnection($this->logHandler);
        $this->raffler->onOpen($host);
        $this->raffler->onMessage($host, json_encode([
            'message' => 'registerHost',
            'hostKey' => '1234',
            'joinCode' => '1234'
        ]));

        $this->assertTrue($this->logHandler->hasInfoThatContains('Provided host key is invalid'));
        $this->assertFalse($this->logHandler->hasInfoThatContains('raffleStarted'));
    }

    /**
     * @test
     */
    public function onMessageHandlesPlayerException(): void
    {
        $this->raffler->onMessage(
            new MockConnection($this->logHandler),
            '🥦😎'
        );

        $this->assertTrue($this->logHandler->hasInfoThatContains('Please specify the type of message'));
    }
}
