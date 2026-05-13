<?php

declare(strict_types=1);

use App\Blog\Database\BlogConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlogConnectionTest extends TestCase
{
    private ModuleDatabaseResolverInterface&MockObject $resolver;
    private BlogConnection $blogConnection;
    private ConnectionInterface&MockObject $connection;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(ModuleDatabaseResolverInterface::class);
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->blogConnection = new BlogConnection($this->resolver);
    }

    public function testGetConnectionReturnsConnectionFromResolver(): void
    {
        $this->resolver
            ->expects($this->once())
            ->method('getConnection')
            ->with('blog', null)
            ->willReturn($this->connection);

        $result = $this->blogConnection->getConnection();

        $this->assertSame($this->connection, $result);
    }

    public function testGetConnectionCachesConnection(): void
    {
        $this->resolver
            ->expects($this->once())
            ->method('getConnection')
            ->with('blog', null)
            ->willReturn($this->connection);

        // First call
        $result1 = $this->blogConnection->getConnection();
        // Second call
        $result2 = $this->blogConnection->getConnection();

        $this->assertSame($this->connection, $result1);
        $this->assertSame($this->connection, $result2);
        $this->assertSame($result1, $result2);
    }
}