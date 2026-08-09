<?php

use PHPUnit\Framework\TestCase;

final class DbTest extends TestCase
{
    public function testConnectionIsMysqliInstance(): void
    {
        require_once __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->assertInstanceOf(mysqli::class, $conn);
        $conn->close();
    }

    public function testConnectionHasNoConnectError(): void
    {
        require_once __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->assertSame(0, $conn->connect_errno);
        $conn->close();
    }
}
