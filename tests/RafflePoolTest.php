<?php

namespace Vos\RaffleServer;

use PHPUnit\Framework\TestCase;
use Exception;
use Vos\RaffleServer\Exception\AddPlayerException;

final class RafflePoolTest extends TestCase
{
    private RafflePool $pool;
    private const JOIN_CODE = '1234';

    protected function setUp(): void
    {
        $this->pool = new RafflePool(2);
    }

    /**
     * @test
     */
    public function closesPool(): void
    {
        $this->startPool();
        $this->assertTrue($this->pool->isActive());
        $this->pool->close();
        $this->assertFalse($this->pool->isActive());
    }

    /**
     * @test
     */
    public function picksWinner(): void
    {
        $this->startPool();
        $this->addPlayer();

        $this->expectOutputRegex('/🏆 And the winner is... boo!/');
        $this->pool->pickWinner();
    }

    /**
     * @test
     */
    public function startsPool(): void
    {
        $this->startPool();
        $this->assertTrue($this->pool->isActive());
    }

    /**
     * @test
     */
    public function addsPlayer(): void
    {
        $this->startPool();
        $this->addPlayer();

        $this->assertEquals(1, $this->pool->playerCount());
    }

    /**
     * @test
     */
    public function removesPlayer(): void
    {
        $connection = new MockConnection();
        $this->startPool();
        $this->pool->addPlayer('1234', new Player('boo', $connection));
        $this->pool->removePlayer($connection);

        $this->assertEquals(0, $this->pool->playerCount());
    }

    /**
     * @test
     */
    public function startPoolFailsForAlreadyActive(): void
    {
        $this->startPool();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can\'t start raffle as it seems to be active?');

        $this->startPool();
    }

    /**
     * @test
     */
    public function pickWinnerFailsForNoPlayers(): void
    {
        $this->expectException(Exception::class);

        $this->startPool();
        $this->pool->pickWinner();
    }

    /**
     * @test
     */
    public function addPlayerFailsForInactivePool(): void
    {
        $this->expectException(AddPlayerException::class);
        $this->expectExceptionMessageMatches('/No active raffle pool/');
        $this->addPlayer();
    }

    /**
     * @test
     */
    public function addPlayerFailsForTooManyPlayers(): void
    {
        $this->startPool();

        $this->expectException(AddPlayerException::class);
        $this->expectExceptionMessageMatches('/Sorry, the raffle pool is full!/');

        $this->addPlayer();
        $this->pool->addPlayer('1234', new Player('bee', new MockConnection()));
        $this->pool->addPlayer('1234', new Player('baa', new MockConnection()));
    }

    /**
     * @test
     */
    public function addPlayerFailsForIncorrectJoinCode(): void
    {
        $this->startPool();

        $this->expectException(AddPlayerException::class);
        $this->expectExceptionMessageMatches('/No active raffle pool for code/');
        $this->pool->addPlayer('1235', new Player('boo', new MockConnection()));
    }

    /**
     * @test
     */
    public function addPlayerFailsForDuplicateUsername(): void
    {
        $this->startPool();

        $this->expectException(AddPlayerException::class);
        $this->expectExceptionMessageMatches('/Username "boo" already taken/');

        $this->addPlayer();
        $this->addPlayer();
    }

    /**
     * @test
     */
    public function removePlayerFailsForNonexistentPlayer(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can\'t remove player that isn\'t there');

        $this->startPool();
        $this->pool->removePlayer(new MockConnection());
    }

    private function startPool(): void
    {
        $this->pool->start('1234', new MockConnection());
    }

    private function addPlayer(): void
    {
        $this->pool->addPlayer(self::JOIN_CODE, new Player('boo', new MockConnection()));
    }
}
