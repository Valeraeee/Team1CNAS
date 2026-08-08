<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions.php';

final class DeleteTest extends TestCase
{
    private mysqli $conn;

    protected function setUp(): void
    {
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->conn = $conn;
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }

    public function testDeleteUserRemovesRow(): void
    {
        $id = createUser($this->conn, 'Test Delete', 'test.delete@example.com');

        $ok = deleteUser($this->conn, $id);

        $this->assertTrue($ok);
        $this->assertNull(getUserById($this->conn, $id));
    }
}