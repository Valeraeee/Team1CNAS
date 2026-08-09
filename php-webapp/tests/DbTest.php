<?php

use PHPUnit\Framework\TestCase;

final class DbTest extends TestCase
{
    public function testConnectionIsMysqliInstance(): void
    {
        //NOSONAR
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->assertInstanceOf(mysqli::class, $conn);
        $conn->close();
    }

    public function testConnectionHasNoConnectError(): void
    {
        // NOSONAR
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->assertSame(0, $conn->connect_errno);
        $conn->close();
    }
}
